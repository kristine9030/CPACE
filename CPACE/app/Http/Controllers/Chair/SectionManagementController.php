<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Section;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SectionManagementController extends Controller
{
    public function index()
    {
        $sections = Section::withCount('faculty')->orderBy('year_level')->orderBy('name')->get();

        // student_profiles.section is a plain string (kept in sync with
        // sections.name at creation), not a foreign key — so section
        // enrollment is counted by name match against active student rows,
        // the same join every other chair student count in the app uses.
        $studentCounts = StudentProfile::query()
            ->join('users', 'users.id', '=', 'student_profiles.user_id')
            ->where('users.role_id', Role::STUDENT)
            ->where('users.is_active', true)
            ->whereNotNull('student_profiles.section')
            ->selectRaw('student_profiles.section, count(*) as aggregate')
            ->groupBy('student_profiles.section')
            ->pluck('aggregate', 'student_profiles.section');

        $sections->each(function (Section $section) use ($studentCounts) {
            $section->student_count = $studentCounts->get($section->name, 0);
        });

        return view('chair.sections', [
            'sections' => $sections,
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
}
