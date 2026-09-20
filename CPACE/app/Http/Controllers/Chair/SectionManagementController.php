<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Section;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SectionManagementController extends Controller
{
    public function index()
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

        return view('chair.sections', [
            'sections' => $sections,
            'unsectioned' => $unsectioned,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:30', Rule::unique('sections', 'name')],
            'year_level' => ['required', 'integer', 'between:1,6'],
        ]);

        Section::create($data);

        return back()->with('status', "Section \"{$data['name']}\" was added.");
    }

    public function update(Request $request, Section $section)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:30', Rule::unique('sections', 'name')->ignore($section->id)],
            'year_level' => ['required', 'integer', 'between:1,6'],
        ]);

        $section->update($data);

        return back()->with('status', "Section \"{$section->name}\" was updated.");
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
