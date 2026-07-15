<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectManagementController extends Controller
{
    public function index()
    {
        return view('chair.subjects', [
            'subjects' => Subject::with([
                'faculty' => fn ($query) => $query->orderBy('first_name'),
                'topics' => fn ($query) => $query->withCount('questions')->orderBy('sort_order')->orderBy('name'),
            ])->orderBy('id')->get(),
        ]);
    }

    public function storeSubject(Request $request)
    {
        $data = $this->validateSubject($request);
        $data['code'] = strtoupper($data['code']);
        Subject::create($data);

        return back()->with('status', "{$data['code']} was added successfully.");
    }

    public function updateSubject(Request $request, Subject $subject)
    {
        $data = $this->validateSubject($request, $subject);
        $data['code'] = strtoupper($data['code']);
        $subject->update($data);

        return back()->with('status', "{$subject->code} was updated successfully.");
    }

    public function destroySubject(Subject $subject)
    {
        if ($subject->topics()->exists() || $subject->faculty()->exists()) {
            return back()->with('error', 'This subject cannot be removed while it has topics or assigned faculty. Remove those links first, or mark the subject inactive.');
        }

        $code = $subject->code;
        $subject->delete();

        return back()->with('status', "{$code} was removed.");
    }

    public function storeTopic(Request $request, Subject $subject)
    {
        $data = $this->validateTopic($request, $subject);
        $subject->topics()->create($data);

        return back()->with('status', "Topic “{$data['name']}” was added to {$subject->code}.");
    }

    public function updateTopic(Request $request, Subject $subject, Topic $topic)
    {
        abort_unless($topic->subject_id === $subject->id, 404);
        $topic->update($this->validateTopic($request, $subject, $topic));

        return back()->with('status', "Topic “{$topic->name}” was updated.");
    }

    public function destroyTopic(Subject $subject, Topic $topic)
    {
        abort_unless($topic->subject_id === $subject->id, 404);

        if ($topic->questions()->exists()) {
            return back()->with('error', 'This topic contains test-bank questions and cannot be removed. Edit it and mark it inactive instead.');
        }

        $name = $topic->name;
        $topic->delete();

        return back()->with('status', "Topic “{$name}” was removed from {$subject->code}.");
    }

    private function validateSubject(Request $request, ?Subject $subject = null): array
    {
        return $request->validate([
            'code' => [
                'required', 'string', 'max:20', 'regex:/^[A-Za-z0-9-]+$/',
                Rule::unique('subjects', 'code')->ignore($subject?->id),
            ],
            'name'              => ['required', 'string', 'max:255'],
            'description'       => ['nullable', 'string', 'max:2000'],
            'passing_threshold' => ['required', 'integer', 'between:1,100'],
            'color'             => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active'         => ['required', 'boolean'],
        ]);
    }

    private function validateTopic(Request $request, Subject $subject, ?Topic $topic = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('topics', 'name')->where(fn ($query) => $query->where('subject_id', $subject->id))->ignore($topic?->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order'   => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active'    => ['required', 'boolean'],
        ]);
    }
}
