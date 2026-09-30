<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use App\Support\CurriculumScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Backs the combined Faculty module (Accounts + Performance Report) on the
 * Program Chair portal — one page, two tabs, so this centralises the two
 * data sets that used to live in ProgramChairController::faculty() and
 * FacultyOversightController::performance() separately.
 */
class FacultyOverviewService
{
    /** Faculty roster + subject/section lookups for the Accounts tab. */
    public function accountsData(): array
    {
        $faculty = User::where('role_id', Role::FACULTY)
            ->with('assignedSubjects', 'assignedSections')
            ->orderBy('first_name')
            ->get();

        $faculty->each(function (User $f) {
            $f->sectionsBySubject = $f->assignedSections
                ->groupBy(fn ($s) => $s->pivot->subject_id)
                ->map(fn ($group) => $group->pluck('id'));
        });

        return [
            'faculty'  => $faculty,
            'subjects' => Subject::orderBy('id')->get(),
            'sections' => Section::where('is_active', true)->orderBy('name')->get(),
        ];
    }

    /** System-wide faculty contribution and question-quality report for the Performance tab. */
    public function performanceData(): array
    {
        $faculty = User::where('role_id', Role::FACULTY)
            ->with('assignedSubjects')
            ->orderBy('first_name')
            ->get();

        // This report is a snapshot of the test bank's CURRENT, live state —
        // "is the active curriculum's bank covered, and who's contributing to
        // it" — so every count on it is scoped to the active curriculum.
        // Questions authored under an archived curriculum are real work, but
        // they're not what the chair is acting on here; they'd otherwise make
        // a freshly-published, empty curriculum look falsely covered.
        $questionAgg = CurriculumScope::restrictToActive(DB::table('questions')
            ->join('topics', 'topics.id', '=', 'questions.topic_id'))
            ->whereNotNull('questions.created_by')
            ->groupBy('questions.created_by')
            ->select(
                'questions.created_by',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN questions.is_active = 1 THEN 1 ELSE 0 END) as active'),
                DB::raw('SUM(CASE WHEN questions.is_active = 0 THEN 1 ELSE 0 END) as draft'),
                DB::raw('MAX(questions.updated_at) as last_contribution')
            )->get()->keyBy('created_by');

        $variantAgg = CurriculumScope::restrictToActive(DB::table('question_variants')
            ->join('questions', 'questions.id', '=', 'question_variants.question_id')
            ->join('topics', 'topics.id', '=', 'questions.topic_id'))
            ->whereNotNull('questions.created_by')
            ->groupBy('questions.created_by')
            ->select('questions.created_by', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'created_by');

        $answerAgg = CurriculumScope::restrictToActive(DB::table('quiz_answers')
            ->join('questions', 'questions.id', '=', 'quiz_answers.question_id')
            ->join('topics', 'topics.id', '=', 'questions.topic_id'))
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
            ->leftJoin('topics', fn ($join) => CurriculumScope::restrictToActive($join->on('topics.subject_id', '=', 'subjects.id')))
            ->leftJoin('questions', 'questions.topic_id', '=', 'topics.id')
            ->groupBy('subjects.id')
            ->select(
                'subjects.id',
                DB::raw('COUNT(questions.id) as total'),
                DB::raw('SUM(CASE WHEN questions.is_active = 1 THEN 1 ELSE 0 END) as active'),
                DB::raw('COUNT(DISTINCT questions.created_by) as contributors')
            )->get()->keyBy('id');

        $subjectAnswerAgg = CurriculumScope::restrictToActive(DB::table('quiz_answers')
            ->join('questions', 'questions.id', '=', 'quiz_answers.question_id')
            ->join('topics', 'topics.id', '=', 'questions.topic_id'))
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

        return compact('stats', 'facultyRows', 'subjectRows');
    }

    /**
     * Question flags use the same thresholds as the faculty quality report.
     * Scoped to the active curriculum for the same reason as performanceData():
     * a flag on a retired question isn't something the chair can still act on.
     */
    private function questionQuality()
    {
        return CurriculumScope::restrictToActive(DB::table('questions')
            ->join('topics', 'topics.id', '=', 'questions.topic_id'))
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
