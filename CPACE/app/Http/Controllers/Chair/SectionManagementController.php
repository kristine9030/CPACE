<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Section;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SectionManagementController extends Controller
{
    /**
     * Sections no longer have a page of their own: they're managed from the
     * Students page (section strip + "Assign section"), so send old links there.
     */
    public function index()
    {
        return redirect()->route('chair.students');
    }

    /**
     * The section catalog with per-section student/faculty counts, plus the
     * number of enrolled students who aren't in any curated section. Shown on
     * the Students page.
     *
     * @return array{sections: \Illuminate\Support\Collection, unsectioned: int}
     */
    public static function catalog(): array
    {
        $sections = Section::orderBy('year_level')->orderBy('name')->get();

        // "Faculty Assigned" per section has to count two different kinds of
        // coverage: a faculty explicitly restricted to this section (a row
        // in faculty_subject_sections), and a faculty given a subject with
        // no section restriction at all - which, per the "no rows = sees the
        // whole subject" rule (App\Models\User::sectionNamesForSubject),
        // already covers every section for that subject. Counting only the
        // first kind made an "All sections" assignment look like it assigned
        // no one, everywhere.
        $restrictedFacultyBySection = DB::table('faculty_subject_sections')
            ->select('section_id', 'faculty_id')->distinct()->get()->groupBy('section_id');

        $unrestrictedFacultyIds = DB::table('faculty_subjects as fs')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))->from('faculty_subject_sections as fss')
                    ->whereColumn('fss.faculty_id', 'fs.faculty_id')
                    ->whereColumn('fss.subject_id', 'fs.subject_id');
            })
            ->distinct()->pluck('fs.faculty_id');

        $sections->each(function (Section $section) use ($restrictedFacultyBySection, $unrestrictedFacultyIds) {
            $restricted = ($restrictedFacultyBySection->get($section->id) ?? collect())->pluck('faculty_id');
            $section->faculty_count = $restricted->merge($unrestrictedFacultyIds)->unique()->count();
        });

        // student_profiles.section is a plain string (kept in sync with
        // sections.name at creation), not a foreign key — so section
        // enrollment is counted by name match against active, currently
        // enrolled student rows (excludes alumni/shifted, same "enrolled"
        // definition used on the chair analytics cohort strip).
        $enrolledStudents = StudentProfile::query()
            ->join('users', 'users.id', '=', 'student_profiles.user_id')
            ->where('users.role_id', Role::STUDENT)
            ->where('users.is_active', true)
            ->where('student_profiles.is_alumni', false)
            ->where('student_profiles.is_shifted', false)
            ->select('student_profiles.section');

        $studentCounts = (clone $enrolledStudents)
            ->whereNotNull('student_profiles.section')
            ->selectRaw('student_profiles.section, count(*) as aggregate')
            ->groupBy('student_profiles.section')
            ->pluck('aggregate', 'student_profiles.section');

        $sections->each(function (Section $section) use ($studentCounts) {
            $section->student_count = $studentCounts->get($section->name, 0);
        });

        // Students who are active/enrolled but whose section string doesn't
        // match any curated section name (typo, legacy value, or never set) —
        // this is why "Total Enrollment" here can be lower than the enrolled
        // count shown elsewhere, so surface it instead of hiding the gap.
        $unsectioned = $enrolledStudents->count()
            - $studentCounts->only($sections->pluck('name')->all())->sum();

        return [
            'sections' => $sections,
            'unsectioned' => $unsectioned,
        ];
    }

    /**
     * Roster for the section modal: who is in this section, and every other
     * enrolled student who could be added (with the section they currently sit
     * in, so the chair sees that adding them moves them).
     */
    public function students(Section $section)
    {
        $rows = $this->enrolledStudents()
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email',
                'student_profiles.student_number', 'student_profiles.section', 'student_profiles.year_level')
            ->orderBy('users.last_name')->orderBy('users.first_name')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'name' => trim($r->last_name . ', ' . $r->first_name),
                'email' => $r->email,
                'student_number' => $r->student_number,
                'section' => $r->section,
                'year_level' => $r->year_level,
            ]);

        return response()->json([
            'members' => $rows->where('section', $section->name)->values(),
            'available' => $rows->where('section', '!=', $section->name)->values(),
        ]);
    }

    public function addStudents(Request $request, Section $section)
    {
        $data = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer'],
        ]);

        // Only enrolled students can be sectioned, whatever ids were posted.
        $ids = $this->enrolledStudents()->whereIn('users.id', $data['student_ids'])->pluck('users.id');

        StudentProfile::whereIn('user_id', $ids)->update([
            'section' => $section->name,
            'year_level' => $section->year_level,
        ]);

        return response()->json(['added' => $ids->count()]);
    }

    private function enrolledStudents()
    {
        return StudentProfile::query()
            ->join('users', 'users.id', '=', 'student_profiles.user_id')
            ->where('users.role_id', Role::STUDENT)
            ->where('users.is_active', true)
            ->where('student_profiles.is_alumni', false)
            ->where('student_profiles.is_shifted', false);
    }

    public function store(Request $request)
    {
        [$name, $yearLevel] = $this->validatedName($request);

        Section::create(['name' => $name, 'year_level' => $yearLevel]);

        return back()->with('status', "Section \"{$name}\" was added.");
    }

    /**
     * Edit (and possibly rename) a section. Students and mock-exam audiences
     * point at a section by its name, so a rename carries them over in the
     * same transaction — nobody drops to "Not in a section", and if any step
     * fails nothing is changed.
     */
    public function update(Request $request, Section $section)
    {
        [$name, $yearLevel] = $this->validatedName($request, $section);
        $oldName = $section->name;
        $yearChanged = $section->year_level !== $yearLevel;

        DB::transaction(function () use ($section, $name, $yearLevel, $oldName, $yearChanged) {
            $section->update(['name' => $name, 'year_level' => $yearLevel]);

            // Every profile still pointing at the old name (alumni included,
            // so their records don't dangle), matched the same forgiving way
            // mock-exam audiences are (case/whitespace-insensitive).
            $members = StudentProfile::whereRaw('LOWER(TRIM(section)) = ?', [mb_strtolower(trim($oldName))]);

            if ($oldName !== $name) {
                $members->update(['section' => $name]);
                $this->renameInMockExamAudiences($oldName, $name);
            }

            // Year level follows the section for current students, the same
            // rule used when a student is placed into a section.
            if ($yearChanged) {
                StudentProfile::where('section', $name)
                    ->where('is_alumni', false)->where('is_shifted', false)
                    ->update(['year_level' => $yearLevel]);
            }
        });

        return back()->with('status', $oldName === $name
            ? "Section \"{$name}\" was updated."
            : "Section \"{$oldName}\" was renamed to \"{$name}\". Its students moved with it.");
    }

    /**
     * Builds "<PROGRAM> <year><sem><no.>" from the form's parts and rejects a
     * name another section already uses.
     *
     * @return array{0: string, 1: int} [name, year level]
     */
    private function validatedName(Request $request, ?Section $ignore = null): array
    {
        $data = $request->validate([
            'program' => ['required', 'string', 'regex:/^[A-Za-z]{2,10}$/'],
            'year_level' => ['required', 'integer', Rule::in(Section::CODE_YEAR_LEVELS)],
            'semester' => ['required', 'integer', Rule::in(array_keys(Section::SEMESTER_LABELS))],
            // Digits, not 'integer': the form sends the padded "01", which
            // Laravel's integer rule rejects because of the leading zero.
            'section_number' => ['required', 'regex:/^\d{1,2}$/', 'not_in:0,00'],
        ], [
            'program.regex' => 'Program must be 2–10 letters, e.g. BSA.',
            'section_number.regex' => 'Section number must be a number from 1 to 99.',
            'section_number.not_in' => 'Section number must be a number from 1 to 99.',
        ]);

        $name = Section::composeName($data['program'], (int) $data['year_level'], (int) $data['semester'], (int) $data['section_number']);

        $taken = Section::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->when($ignore, fn ($q) => $q->whereKeyNot($ignore->id))
            ->exists();
        if ($taken) {
            throw ValidationException::withMessages(['name' => "A section named \"{$name}\" already exists."]);
        }

        return [$name, (int) $data['year_level']];
    }

    private function renameInMockExamAudiences(string $oldName, string $newName): void
    {
        $old = mb_strtolower(trim($oldName));

        DB::table('mock_exams')->whereNotNull('audience_sections')->get(['id', 'audience_sections'])
            ->each(function ($exam) use ($old, $newName) {
                $sections = json_decode($exam->audience_sections, true);
                if (! is_array($sections)) {
                    return;
                }
                $renamed = array_map(fn ($s) => mb_strtolower(trim((string) $s)) === $old ? $newName : $s, $sections);
                if ($renamed !== $sections) {
                    DB::table('mock_exams')->where('id', $exam->id)
                        ->update(['audience_sections' => json_encode(array_values(array_unique($renamed)))]);
                }
            });
    }

    public function toggle(Section $section)
    {
        $section->update(['is_active' => !$section->is_active]);

        $status = $section->is_active ? 'activated' : 'deactivated';

        return back()->with('status', "Section \"{$section->name}\" was {$status}.");
    }

    public function destroy(Section $section)
    {
        $studentCount = StudentProfile::query()
            ->join('users', 'users.id', '=', 'student_profiles.user_id')
            ->where('users.role_id', Role::STUDENT)
            ->where('users.is_active', true)
            ->where('student_profiles.is_alumni', false)
            ->where('student_profiles.is_shifted', false)
            ->where('student_profiles.section', $section->name)
            ->count();

        if ($studentCount > 0 || $section->faculty()->exists()) {
            return back()->with('error', "\"{$section->name}\" cannot be removed while it still has enrolled students or faculty assigned to it. Reassign them first, or deactivate the section instead.");
        }

        $name = $section->name;
        $section->delete();

        return back()->with('status', "Section \"{$name}\" was removed.");
    }
}
