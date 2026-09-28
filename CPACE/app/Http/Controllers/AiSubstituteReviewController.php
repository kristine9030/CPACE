<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
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
    public function index()
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

        return view('ai-substitute-review', [
            'pending' => $pending,
            'pendingCount' => $pending->flatten()->count(),
            'recent' => $recent,
            'isChair' => $user->isChair(),
        ]);
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
}
