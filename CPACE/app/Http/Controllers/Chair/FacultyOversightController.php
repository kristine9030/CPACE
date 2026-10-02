<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Role;
use App\Models\User;
use App\Services\FacultyOverviewService;
use App\Support\CurriculumScope;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FacultyOversightController extends Controller
{
    /** Per-faculty contribution history and test-bank impact. */
    public function activity(int $id)
    {
        $faculty = User::where('role_id', Role::FACULTY)
            ->with('assignedSubjects')
            ->findOrFail($id);

        // Scoped to the active curriculum, same reasoning as performance():
        // this page is reached from "Needs Your Attention" rows that are
        // themselves scoped, so it must show the same "nothing yet" picture
        // rather than surfacing questions from a retired curriculum.
        $questions = CurriculumScope::restrictToActive(Question::query()
            ->join('topics', 'topics.id', '=', 'questions.topic_id'))
            ->where('questions.created_by', $faculty->id)
            ->select('questions.*')
            ->with(['topic.subject'])
            ->orderByDesc('questions.updated_at')
            ->get();

        $questionIds = $questions->pluck('id')->all();
        $answerStats = $this->answerStatsForQuestions($questionIds);
        $variantCounts = empty($questionIds)
            ? collect()
            : DB::table('question_variants')->whereIn('question_id', $questionIds)
                ->groupBy('question_id')->select('question_id', DB::raw('COUNT(*) as total'))
                ->pluck('total', 'question_id');

        $answered = (int) $answerStats->sum('answered');
        $correct = (int) $answerStats->sum('correct');
        $stats = [
            'questions' => $questions->count(),
            'active'    => $questions->where('is_active', true)->count(),
            'variants'  => (int) $variantCounts->sum(),
            'accuracy'  => $answered > 0 ? (int) round($correct / $answered * 100) : null,
            'answered'  => $answered,
        ];

        $subjectContributions = $questions->groupBy(fn (Question $question) => $question->topic?->subject?->id)
            ->filter(fn ($group, $subjectId) => $subjectId !== null && $subjectId !== '')
            ->map(function ($group) use ($answerStats) {
                $subject = $group->first()->topic->subject;
                $ids = $group->pluck('id');
                $subjectAnswers = $answerStats->only($ids->all());
                $answered = (int) $subjectAnswers->sum('answered');
                $correct = (int) $subjectAnswers->sum('correct');

                return [
                    'code'      => $subject->code,
                    'name'      => $subject->name,
                    'questions' => $group->count(),
                    'active'    => $group->where('is_active', true)->count(),
                    'answered'  => $answered,
                    'accuracy'  => $answered > 0 ? (int) round($correct / $answered * 100) : null,
                ];
            })->sortByDesc('questions')->values();

        $allEvents = $questions->flatMap(function (Question $question) use ($answerStats, $variantCounts) {
            $base = [
                'question_id' => $question->id,
                'text'        => $question->question_text,
                'subject'     => $question->topic?->subject?->code ?? '—',
                'topic'       => $question->topic?->name ?? 'Unknown topic',
                'active'      => $question->is_active,
                'answered'    => (int) ($answerStats->get($question->id)->answered ?? 0),
                'variants'    => (int) ($variantCounts[$question->id] ?? 0),
            ];

            $events = [array_merge($base, ['type' => 'created', 'date' => Carbon::parse($question->created_at)])];
            if ($question->updated_at && $question->created_at
                && $question->updated_at->greaterThan($question->created_at->copy()->addSecond())) {
                $events[] = array_merge($base, ['type' => 'updated', 'date' => Carbon::parse($question->updated_at)]);
            }

            return $events;
        })->sortByDesc('date')->values();

        $eventsPerPage = 10;
        $currentPage = max(1, LengthAwarePaginator::resolveCurrentPage('activity_page'));
        $events = new LengthAwarePaginator(
            $allEvents->forPage($currentPage, $eventsPerPage)->values(),
            $allEvents->count(),
            $eventsPerPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'activity_page',
            ]
        );

        return view('chair.faculty-activity', compact(
            'faculty', 'stats', 'subjectContributions', 'events'
        ));
    }

    /** System-wide faculty contribution and question-quality report (Performance tab of the combined Faculty module). */
    public function performance(FacultyOverviewService $overview)
    {
        return view('chair.faculty', array_merge(
            $overview->accountsData(),
            $overview->performanceData()
        ));
    }

    private function answerStatsForQuestions(array $questionIds)
    {
        if (empty($questionIds)) {
            return collect();
        }

        return DB::table('quiz_answers')->whereIn('question_id', $questionIds)
            ->whereNotNull('is_correct')->groupBy('question_id')
            ->select(
                'question_id',
                DB::raw('COUNT(*) as answered'),
                DB::raw('COALESCE(SUM(is_correct), 0) as correct')
            )->get()->keyBy('question_id');
    }
}
