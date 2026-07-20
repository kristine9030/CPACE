<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SubjectController extends Controller
{
    /**
     * Subjects grid — live counts for topics, questions and weak topics.
     */
    public function index()
    {
        $studentId = Auth::id();

        $subjects = Subject::where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(function ($subject) use ($studentId) {
                $topicIds = $subject->topics()->where('is_active', true)->pluck('id');

                $questionCount = DB::table('questions')
                    ->where('is_active', true)
                    ->whereIn('topic_id', $topicIds)
                    ->count();

                // A "weak" topic is one the student has attempted where their
                // accuracy sits below this subject's passing threshold.
                $weakTopics = DB::table('performance_records')
                    ->where('student_id', $studentId)
                    ->whereIn('topic_id', $topicIds)
                    ->where('total_attempts', '>', 0)
                    ->whereRaw('(correct_count / total_attempts) * 100 < ?', [$subject->passing_threshold])
                    ->count();

                $subject->setAttribute('topic_count', $topicIds->count());
                $subject->setAttribute('question_count', $questionCount);
                $subject->setAttribute('weak_count', $weakTopics);

                return $subject;
            });

        return view('student.subjects', compact('subjects'));
    }

    /**
     * Topic list for a single subject.
     */
    public function show(Subject $subject)
    {
        abort_unless($subject->is_active, 404);

        $topics = Topic::where('subject_id', $subject->id)
            ->where('is_active', true)
            ->withCount([
                'questions as question_count' => fn ($q) => $q->where('is_active', true),
                'materials as material_count' => fn ($q) => $q->where('is_active', true),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('student.subject-topics', compact('subject', 'topics'));
    }

    /**
     * Study materials attached to a single topic.
     */
    public function topic(Subject $subject, Topic $topic)
    {
        abort_unless($topic->subject_id === $subject->id, 404);

        $materials = Material::where('topic_id', $topic->id)
            ->where('is_active', true)
            ->with('uploader')
            ->orderByDesc('id')
            ->get();

        return view('student.topic-materials', compact('subject', 'topic', 'materials'));
    }

    /**
     * Stream a material file as a download.
     */
    public function download(Material $material)
    {
        abort_if($material->kind !== 'file' || ! $material->file_path, 404);
        abort_unless(Storage::disk('public')->exists($material->file_path), 404);

        return Storage::disk('public')->download(
            $material->file_path,
            $material->original_name ?: basename($material->file_path)
        );
    }
}
