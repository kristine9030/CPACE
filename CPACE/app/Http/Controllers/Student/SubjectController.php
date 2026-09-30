<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Concerns\SubjectTheme;
use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\WeaknessDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    use SubjectTheme;

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
                // What's available to practice right now: the topics of the
                // curriculum covering this student's batch, so the question
                // count reflects what a new quiz can actually draw from.
                $topicIds = $subject->topics()->forStudent($studentId)->where('is_active', true)->pluck('id');

                // What the student has actually done: EVERY topic this subject
                // has ever had, across every curriculum. A curriculum change
                // must never make a student's quiz history, weak areas or
                // accuracy disappear — those stay keyed to the topic they were
                // recorded against, not to whichever curriculum is active today.
                $allTopicIds = $subject->topics()->pluck('id');

                $questionCount = DB::table('questions')
                    ->where('is_active', true)
                    ->whereIn('topic_id', $topicIds)
                    ->count();

                // A "weak" topic is decided by the shared WeaknessDetector (under
                // 60% over 5+ attempts, or 3 wrong in a row) - the same rule as the
                // dashboard, Performance page, Calendar, faculty and chair views.
                $detector = app(WeaknessDetector::class);
                $weakTopics = DB::table('performance_records')
                    ->where('student_id', $studentId)
                    ->whereIn('topic_id', $allTopicIds)
                    ->where('total_attempts', '>', 0)
                    ->get(['total_attempts', 'correct_count', 'consecutive_wrong'])
                    ->filter(fn ($r) => $detector->evaluate($r)[0])
                    ->count();

                // Overall subject accuracy = correct answers / attempts summed
                // across every topic and subtopic in this subject (not an
                // average of per-topic percentages — a rollup of raw counts,
                // so heavily-attempted topics weigh more than lightly-touched ones).
                $totals = DB::table('performance_records')
                    ->where('student_id', $studentId)
                    ->whereIn('topic_id', $allTopicIds)
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
            ->forStudent(Auth::id())
            ->where('is_active', true)
            ->withCount([
                'questions as question_count' => fn ($q) => $q->where('is_active', true),
                'materials as material_count' => fn ($q) => $q->where('is_active', true),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $topicTree = Topic::buildTree($topics);

        // Per-topic rows only ever cover the topics on screen (the active
        // curriculum's tree) — a row for a topic that isn't shown would be
        // meaningless. But the subject-level "Overall accuracy" badge must
        // match the Subjects grid, so it's summed separately across every
        // topic this subject has EVER had, not just the visible tree.
        $performanceByTopicId = DB::table('performance_records')
            ->where('student_id', Auth::id())
            ->whereIn('topic_id', $topics->pluck('id'))
            ->get()
            ->keyBy('topic_id');

        Topic::attachProgress($topicTree, $performanceByTopicId);

        $overallTotals = DB::table('performance_records')
            ->where('student_id', Auth::id())
            ->whereIn('topic_id', $subject->topics()->pluck('id'))
            ->selectRaw('SUM(total_attempts) as attempts, SUM(correct_count) as correct')
            ->first();
        $overallAttempts = (int) ($overallTotals->attempts ?? 0);
        $overallCorrect = (int) ($overallTotals->correct ?? 0);
        $overallAccuracy = $overallAttempts > 0 ? (int) round($overallCorrect / $overallAttempts * 100) : null;

        // Same per-subject colour and icon as the Class Quizzes header.
        $theme = self::theme($subject->code);
        $subjectIcon = self::subjectIcon($subject->code);

        return view('student.subject-topics', compact(
            'subject', 'topics', 'topicTree', 'overallAttempts', 'overallCorrect', 'overallAccuracy',
            'theme', 'subjectIcon'
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
                // Office Online can't log in, so Word/Excel/PowerPoint get a short-lived signed link.
                'view_url'      => in_array($m->file_category, ['word', 'excel', 'powerpoint'], true)
                    ? $m->previewUrl()
                    : $m->url(),
            ];
        })->values();

        return view('student.topic-materials', compact('subject', 'topic', 'materials', 'materialsJson'));
    }
}
