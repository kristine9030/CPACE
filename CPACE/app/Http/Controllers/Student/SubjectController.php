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

                // Overall subject accuracy = correct answers / attempts summed
                // across every topic and subtopic in this subject (not an
                // average of per-topic percentages — a rollup of raw counts,
                // so heavily-attempted topics weigh more than lightly-touched ones).
                $totals = DB::table('performance_records')
                    ->where('student_id', $studentId)
                    ->whereIn('topic_id', $topicIds)
                    ->selectRaw('SUM(total_attempts) as attempts, SUM(correct_count) as correct')
                    ->first();

                $subject->setAttribute('topic_count', $topicIds->count());
                $subject->setAttribute('question_count', $questionCount);
                $subject->setAttribute('weak_count', $weakTopics);
                $subject->setAttribute('overall_attempts', (int) ($totals->attempts ?? 0));
                $subject->setAttribute('overall_correct', (int) ($totals->correct ?? 0));
                $subject->setAttribute('overall_accuracy', $totals->attempts > 0 ? (int) round($totals->correct / $totals->attempts * 100) : null);

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

        $topicTree = Topic::buildTree($topics);

        $performanceByTopicId = DB::table('performance_records')
            ->where('student_id', Auth::id())
            ->whereIn('topic_id', $topics->pluck('id'))
            ->get()
            ->keyBy('topic_id');

        Topic::attachProgress($topicTree, $performanceByTopicId);

        // Overall subject accuracy = correct/attempts summed across every
        // topic and subtopic in this subject — same rollup rule as the
        // Subjects grid card, computed here from the records already fetched
        // above instead of a second query.
        $overallAttempts = (int) $performanceByTopicId->sum('total_attempts');
        $overallCorrect = (int) $performanceByTopicId->sum('correct_count');
        $overallAccuracy = $overallAttempts > 0 ? (int) round($overallCorrect / $overallAttempts * 100) : null;

        return view('student.subject-topics', compact(
            'subject', 'topics', 'topicTree', 'overallAttempts', 'overallCorrect', 'overallAccuracy'
        ));
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

        $materialsJson = $materials->map(function (Material $m) {
            $meta = $m->iconMeta();

            return [
                'id'            => $m->id,
                'title'         => $m->title,
                'file_category' => $m->file_category,
                'original_name' => $m->original_name,
                'file_size'     => $m->humanSize(),
                'uploader_name' => $m->uploader->name ?? 'Faculty',
                'icon'          => $meta['icon'],
                'color'         => $meta['color'],
                'view_url'      => $m->url(),
                'download_url'  => route('materials.download', $m->id),
            ];
        })->values();

        return view('student.topic-materials', compact('subject', 'topic', 'materials', 'materialsJson'));
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
