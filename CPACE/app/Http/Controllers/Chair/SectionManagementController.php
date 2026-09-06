<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SectionManagementController extends Controller
{
    public function index()
    {
        return view('chair.sections', [
            'sections' => Section::withCount('faculty')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:30', Rule::unique('sections', 'name')],
        ]);

        Section::create($data);

        return back()->with('status', "Section \"{$data['name']}\" was added.");
    }

    public function toggle(Section $section)
    {
        $section->update(['is_active' => !$section->is_active]);

        $status = $section->is_active ? 'activated' : 'deactivated';

        return back()->with('status', "Section \"{$section->name}\" was {$status}.");
    }
}
