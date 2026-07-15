<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Role;
use App\Models\Subject;
use App\Models\User;
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

        $questions = Question::query()
            ->where('created_by', $faculty->id)
            ->with(['topic.subject'])
            ->orderByDesc('updated_at')
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

        $events = $questions->flatMap(function (Question $question) use ($answerStats, $variantCounts) {
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
        })->sortByDesc('date')->take(50)->values();

        return view('chair.faculty-activity', compact(
            'faculty', 'stats', 'subjectContributions', 'events'
        ));
    }

    /** System-wide faculty contribution and question-quality report. */
    public function performance()
    {
        $faculty = User::where('role_id', Role::FACULTY)
            ->with('assignedSubjects')
            ->orderBy('first_name')
            ->get();

        $questionAgg = DB::table('questions')
            ->whereNotNull('created_by')
            ->groupBy('created_by')
            ->select(
                'created_by',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active'),
                DB::raw('SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as draft'),
                DB::raw('MAX(updated_at) as last_contribution')
            )->get()->keyBy('created_by');

        $variantAgg = DB::table('question_variants')
            ->join('questions', 'questions.id', '=', 'question_variants.question_id')
            ->whereNotNull('questions.created_by')
            ->groupBy('questions.created_by')
            ->select('questions.created_by', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'created_by');

        $answerAgg = DB::table('quiz_answers')
            ->join('questions', 'questions.id', '=', 'quiz_answers.question_id')
            ->whereNotNull('quiz_answers.is_correct')
            ->whereNotNull('questions.created_by')
            ->groupBy('questions.created_by')
            ->select(
                'questions.created_by',
                DB::raw('COUNT(*) as answered'),
                DB::raw('COALESCE(SUM(quiz_answers.is_correct), 0) as correct')
            )->get()->keyBy('created_by');

        $qualityByFaculty = $this->questionQuality()->groupBy('created_by');
        $facultyRows = $faculty->map(function (User $member) use ($questionAgg, $variantAgg, $answerAgg, $qualityByFaculty) {
            $questions = $questionAgg->get($member->id);
            $answers = $answerAgg->get($member->id);
            $answered = (int) ($answers->answered ?? 0);
            $quality = $qualityByFaculty->get($member->id, collect());

            return [
                'id'                => $member->id,
                'name'              => $member->name,
                'initials'          => strtoupper(substr($member->first_name, 0, 1).substr($member->last_name, 0, 1)),
                'subjects'          => $member->assignedSubjects->pluck('code')->all(),
                'questions'         => (int) ($questions->total ?? 0),
                'active'            => (int) ($questions->active ?? 0),
                'draft'             => (int) ($questions->draft ?? 0),
                'variants'          => (int) ($variantAgg[$member->id] ?? 0),
                'answered'          => $answered,
                'accuracy'          => $answered > 0 ? (int) round((int) $answers->correct / $answered * 100) : null,
                'quality_flags'     => $quality->where('flag', '!=', 'healthy')->count(),
                'unused'            => $quality->where('flag', 'unused')->count(),
                'last_contribution' => $questions?->last_contribution
                    ? Carbon::parse($questions->last_contribution) : null,
            ];
        })->sortByDesc('questions')->values();

        $subjectQuestionAgg = DB::table('subjects')
            ->leftJoin('topics', 'topics.subject_id', '=', 'subjects.id')
            ->leftJoin('questions', 'questions.topic_id', '=', 'topics.id')
            ->groupBy('subjects.id')
            ->select(
                'subjects.id',
                DB::raw('COUNT(questions.id) as total'),
                DB::raw('SUM(CASE WHEN questions.is_active = 1 THEN 1 ELSE 0 END) as active'),
                DB::raw('COUNT(DISTINCT questions.created_by) as contributors')
            )->get()->keyBy('id');

        $subjectAnswerAgg = DB::table('quiz_answers')
            ->join('questions', 'questions.id', '=', 'quiz_answers.question_id')
            ->join('topics', 'topics.id', '=', 'questions.topic_id')
            ->whereNotNull('quiz_answers.is_correct')
            ->groupBy('topics.subject_id')
            ->select(
                'topics.subject_id',
                DB::raw('COUNT(*) as answered'),
                DB::raw('COALESCE(SUM(quiz_answers.is_correct), 0) as correct')
            )->get()->keyBy('subject_id');

        $subjects = Subject::withCount('faculty')->orderBy('id')->get();
        $maxQuestions = max(1, (int) $subjectQuestionAgg->max('total'));
        $subjectRows = $subjects->map(function (Subject $subject) use ($subjectQuestionAgg, $subjectAnswerAgg, $maxQuestions) {
            $questions = $subjectQuestionAgg->get($subject->id);
            $answers = $subjectAnswerAgg->get($subject->id);
            $answered = (int) ($answers->answered ?? 0);
            $total = (int) ($questions->total ?? 0);

            return [
                'code'             => $subject->code,
                'name'             => $subject->name,
                'questions'        => $total,
                'active'           => (int) ($questions->active ?? 0),
                'contributors'     => (int) ($questions->contributors ?? 0),
                'assigned_faculty' => $subject->faculty_count,
                'answered'         => $answered,
                'accuracy'         => $answered > 0 ? (int) round((int) $answers->correct / $answered * 100) : null,
                'width'            => (int) round($total / $maxQuestions * 100),
            ];
        })->sortByDesc('questions')->values();

        $totalAnswered = (int) $answerAgg->sum('answered');
        $stats = [
            'questions'    => (int) $questionAgg->sum('total'),
            'contributors' => $facultyRows->where('questions', '>', 0)->count(),
            'accuracy'     => $totalAnswered > 0
                ? (int) round((int) $answerAgg->sum('correct') / $totalAnswered * 100) : null,
            'flags'        => $facultyRows->sum('quality_flags'),
        ];

        return view('chair.faculty-performance', compact('stats', 'facultyRows', 'subjectRows'));
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

    /** Question flags use the same thresholds as the faculty quality report. */
    private function questionQuality()
    {
        return DB::table('questions')
            ->leftJoin('quiz_answers', function ($join) {
                $join->on('quiz_answers.question_id', '=', 'questions.id')
                    ->whereNotNull('quiz_answers.is_correct');
            })
            ->whereNotNull('questions.created_by')
            ->where('questions.is_active', true)
            ->groupBy('questions.id', 'questions.created_by')
            ->select(
                'questions.id', 'questions.created_by',
                DB::raw('COUNT(quiz_answers.id) as answered'),
                DB::raw('COALESCE(SUM(quiz_answers.is_correct), 0) as correct')
            )->get()->map(function ($question) {
                $answered = (int) $question->answered;
                $accuracy = $answered > 0 ? (int) round((int) $question->correct / $answered * 100) : 0;
                $flag = match (true) {
                    $answered === 0 => 'unused',
                    $answered >= 5 && $accuracy < 40 => 'too_hard',
                    $answered >= 5 && $accuracy > 95 => 'too_easy',
                    default => 'healthy',
                };

                return ['created_by' => (int) $question->created_by, 'flag' => $flag];
            });
    }
}
