<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Topic;
use App\Models\User;
use App\Services\CurriculumGapFiller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Review queue for AI substitute questions (drafted by curriculum:fill-gaps
 * when a topic stayed short of its TOS item count). Shared by the faculty
 * route group (their assigned subjects) and the chair's (every subject).
 * Approving makes the question live; rejecting keeps it hidden for good.
 */
class AiSubstituteReviewController extends Controller
{
    public function index(CurriculumGapFiller $filler)
    {
        $user = Auth::user();

        $pending = $this->scoped($user, Question::pendingAiReview())
            ->with(['choices' => fn ($q) => $q->orderBy('choice_label'), 'topic.subject', 'topic.parent'])
            ->orderBy('questions.topic_id')
            ->orderBy('questions.id')
            ->get()
            ->groupBy(fn (Question $q) => $q->topic->subject->code ?? '—');

        $recent = $this->scoped($user, Question::query()
            ->where('questions.source', Question::SOURCE_AI_SUBSTITUTE)
            ->whereIn('questions.review_status', [Question::REVIEW_APPROVED, Question::REVIEW_REJECTED]))
            ->with(['topic.subject', 'reviewer'])
            ->orderByDesc('questions.reviewed_at')
            ->limit(15)
            ->get();

        $shortlist = $this->scopedShortlist($user, $filler->shortlist())
            ->sortBy(fn ($row) => $row['topic']->name)
            ->groupBy(fn ($row) => $row['topic']->subject->code ?? '—');

        return view('ai-substitute-review', [
            'pending' => $pending,
            'pendingCount' => $pending->flatten()->count(),
            'recent' => $recent,
            'shortlist' => $shortlist,
            'isChair' => $user->isChair(),
        ]);
    }

    /** Manually draft substitutes for one short topic now, skipping the grace-period wait. */
    public function generate(Request $request, Topic $topic, CurriculumGapFiller $filler)
    {
        $user = Auth::user();

        if (! $user->isChair() && ! $user->assignedSubjects()->where('subjects.id', $topic->subject_id)->exists()) {
            return back()->with('warning', "You're not assigned to this subject, so you can't generate questions for it.");
        }

        // Up to gap_fill_max_per_topic sequential AI calls, each with a Gemini->OpenRouter
        // fallback (45s timeout apiece) — comfortably past PHP's default 60s limit.
        set_time_limit(300);

        $result = $filler->generateNow($topic);

        if ($result['shortBy'] === 0) {
            return back()->with('status', "\"{$topic->name}\" already meets its TOS item count.");
        }
        if ($result['drafted'] === 0) {
            return back()->with('warning', 'The AI could not draft questions right now — try again shortly.');
        }

        return back()->with('status', "Drafted {$result['drafted']} question(s) for \"{$topic->name}\" — review them below.");
    }

    public function approve(int $id)
    {
        return $this->decide($id, true);
    }

    public function reject(int $id)
    {
        return $this->decide($id, false);
    }

    private function decide(int $id, bool $approve)
    {
        $user = Auth::user();
        $question = Question::with(['topic', 'choices'])->findOrFail($id);

        if (! $this->canReview($user, $question)) {
            return back()->with('warning', "You're not assigned to this subject, so you can't review its questions.");
        }
        if (! $question->isPendingAiReview()) {
            return back()->with('warning', 'This question was already reviewed.');
        }
        if ($approve && ! $question->choices->contains('is_correct', true)) {
            return back()->with('warning', 'This question has no correct answer marked, so it cannot go live. Edit it first or reject it.');
        }

        DB::transaction(fn () => $question->update([
            'is_active' => $approve,
            'review_status' => $approve ? Question::REVIEW_APPROVED : Question::REVIEW_REJECTED,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]));

        return back()->with('status', $approve
            ? 'Approved — the question is now live in the Test Bank.'
            : 'Rejected — the question stays hidden from students.');
    }

    private function canReview(User $user, Question $question): bool
    {
        return $user->isChair()
            || $user->assignedSubjects()->where('subjects.id', $question->topic->subject_id)->exists();
    }

    /** Chair sees every subject; faculty only the subjects assigned to them. */
    private function scoped(User $user, $query)
    {
        if ($user->isChair()) {
            return $query;
        }

        return $query->whereHas('topic', fn ($t) => $t->whereIn('subject_id', $user->assignedSubjects()->pluck('subjects.id')));
    }

    /** Same chair-sees-all / faculty-sees-assigned split, for the shortlist() collection. */
    private function scopedShortlist(User $user, Collection $shortlist): Collection
    {
        if ($user->isChair()) {
            return $shortlist;
        }

        $subjectIds = $user->assignedSubjects()->pluck('subjects.id')->all();

        return $shortlist->filter(fn ($row) => in_array($row['topic']->subject_id, $subjectIds));
    }
}
