<?php

namespace App\Services;

use App\Models\CurriculumAudit;
use App\Models\CurriculumVersion;
use App\Models\Question;
use App\Models\Role;
use App\Models\Topic;
use App\Models\User;
use App\Support\CurriculumAuditor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * The safety net behind curriculum:fill-gaps. Every topic of the published
 * curriculum with a TOS item count is its faculty's quota: the questions in
 * the topic and its subtopics should reach that count.
 *
 * A topic that falls short gets a first notice to its faculty, a final warning
 * (faculty and chair) shortly before the grace period ends, and once it has
 * passed, the missing items are drafted by AI. Those drafts are saved inactive
 * with review_status "pending", so no student sees one until the faculty or
 * the chair approves it. Faculty stay responsible for the bank; this only
 * keeps students from being short-changed while they catch up.
 */
class CurriculumGapFiller
{
    /** TOS difficulty mix: Easy 30%, Moderate 40%, Difficult 30%. */
    private const DIFFICULTY_MIX = ['easy' => 0.3, 'moderate' => 0.4, 'difficult' => 0.3];

    private const DIFFICULTY_LABELS = ['easy' => 'Easy', 'moderate' => 'Medium', 'difficult' => 'Hard'];

    public function __construct(private AiQuestionAssistantService $ai)
    {
    }

    /**
     * @return array{checked: int, short: int, noticed: int, warned: int, drafted: int, failed: int}
     */
    public function run(?Carbon $now = null): array
    {
        $now ??= now();
        $summary = ['checked' => 0, 'short' => 0, 'noticed' => 0, 'warned' => 0, 'drafted' => 0, 'failed' => 0];

        if (! Schema::hasColumn('topics', 'gap_flagged_at') || ! Schema::hasColumn('topics', 'tos_items')) {
            return $summary;
        }

        $topics = Topic::inActiveCurriculum()->with('subject')->where('is_active', true)->get()->keyBy('id');
        $children = $topics->groupBy('parent_id');

        foreach ($topics as $topic) {
            if ((int) $topic->tos_items <= 0 || $this->hasTargetedAncestor($topic, $topics)) {
                continue; // no quota here, or a parent area's quota already covers this subtree
            }

            $summary['checked']++;
            $subtree = $this->subtree($topic, $children);
            $counts = $this->questionCounts($subtree->pluck('id')->all());
            $shortBy = (int) $topic->tos_items - $counts['live'] - $counts['pending'];

            if ($shortBy <= 0) {
                if ($topic->gap_flagged_at || $topic->gap_warned_at) {
                    $topic->update(['gap_flagged_at' => null, 'gap_warned_at' => null]);
                }
                continue;
            }

            $summary['short']++;

            if (! $topic->gap_flagged_at) {
                $topic->update(['gap_flagged_at' => $now]);
                $this->notifyFirstNotice($topic, $shortBy, $now);
                $summary['noticed']++;
                continue;
            }

            $deadline = $topic->gap_flagged_at->copy()->addDays($this->graceDays());

            if ($now->lt($deadline)) {
                $warnFrom = $deadline->copy()->subDays((int) config('curriculum.gap_fill_warning_days', 3));
                if (! $topic->gap_warned_at && $now->gte($warnFrom)) {
                    $topic->update(['gap_warned_at' => $now]);
                    $this->notifyFinalWarning($topic, $shortBy, $deadline);
                    $summary['warned']++;
                }
                continue;
            }

            $budget = max(0, (int) config('curriculum.gap_fill_max_per_run', 40)) - $summary['drafted'];
            if ($budget <= 0) {
                continue; // this night's AI budget is spent; the rest wait for the next run
            }

            [$drafted, $failed] = $this->draftSubstitutes($topic, $subtree, $children, min($shortBy, $this->maxPerTopic(), $budget));
            $summary['drafted'] += $drafted;
            $summary['failed'] += $failed;

            if ($drafted > 0) {
                $this->notifyDrafted($topic, $drafted);
                $this->audit($topic, $drafted);
            }
        }

        $this->flushNotifications();

        return $summary;
    }

    /**
     * Draft up to $count questions into the topic's subtree, spreading them
     * over the least-covered subtopics and the least-covered difficulty.
     *
     * @return array{0: int, 1: int} [drafted, failed]
     */
    private function draftSubstitutes(Topic $target, Collection $subtree, Collection $children, int $count): array
    {
        // Questions go into the most specific topics (leaves) of the area.
        $leaves = $subtree->filter(fn (Topic $t) => ! $children->has($t->id))->values();
        if ($leaves->isEmpty()) {
            $leaves = collect([$target]);
        }

        $ids = $subtree->pluck('id')->all();
        $perTopic = $this->countable()->whereIn('topic_id', $ids)
            ->select('topic_id', DB::raw('COUNT(*) as n'))->groupBy('topic_id')->pluck('n', 'topic_id')->map(fn ($n) => (int) $n);
        $perDifficulty = $this->countable()->whereIn('topic_id', $ids)
            ->select('difficulty', DB::raw('COUNT(*) as n'))->groupBy('difficulty')->pluck('n', 'difficulty')->map(fn ($n) => (int) $n);
        $existing = Question::whereIn('topic_id', $ids)->orderByDesc('id')->limit(30)->pluck('question_text')->all();

        $drafted = 0;
        for ($i = 0; $i < $count; $i++) {
            $leaf = $leaves->sortBy(fn (Topic $t) => [$perTopic[$t->id] ?? 0, $t->sort_order, $t->id])->first();
            $difficulty = $this->nextDifficulty($perDifficulty);
            $topicLabel = $leaf->id === $target->id ? $target->name : "{$target->name} — {$leaf->name}";

            try {
                $draft = $this->ai->draftQuestion(
                    $target->subject->name ?? $target->subject->code ?? 'CPA Reviewer',
                    $topicLabel,
                    self::DIFFICULTY_LABELS[$difficulty],
                    'mcq',
                    $existing,
                    null
                );
            } catch (\Throwable $e) {
                // Most likely the AI provider is down: stop this topic, retry next run.
                Log::warning('AI gap fill: draft failed.', ['topic_id' => $target->id, 'error' => $e->getMessage()]);

                return [$drafted, $count - $drafted];
            }

            $this->saveDraft($leaf, $difficulty, $draft);

            $drafted++;
            $perTopic[$leaf->id] = ($perTopic[$leaf->id] ?? 0) + 1;
            $perDifficulty[$difficulty] = ($perDifficulty[$difficulty] ?? 0) + 1;
            array_unshift($existing, $draft['question_text']);
        }

        return [$drafted, 0];
    }

    private function saveDraft(Topic $topic, string $difficulty, array $draft): Question
    {
        return DB::transaction(function () use ($topic, $difficulty, $draft) {
            $question = Question::create([
                'topic_id' => $topic->id,
                'created_by' => null,
                'source' => Question::SOURCE_AI_SUBSTITUTE,
                'question_text' => $draft['question_text'],
                'question_type' => 'mcq',
                'difficulty' => $difficulty,
                'explanation' => $draft['explanation'] !== '' ? $draft['explanation'] : null,
                'is_active' => false,
                'review_status' => Question::REVIEW_PENDING,
            ]);

            foreach ($draft['choices'] as $label => $choice) {
                $question->choices()->create([
                    'choice_label' => strtoupper($label),
                    'choice_text' => $choice['text'],
                    'is_correct' => $choice['is_correct'],
                ]);
            }

            return $question;
        });
    }

    /** The difficulty furthest below its share of the TOS mix. */
    private function nextDifficulty(Collection $perDifficulty): string
    {
        $total = $perDifficulty->sum() + 1;

        return collect(self::DIFFICULTY_MIX)
            ->map(fn ($share, $level) => $share * $total - ($perDifficulty[$level] ?? 0))
            ->sortDesc()
            ->keys()
            ->first();
    }

    /**
     * Questions that count toward a quota: live ones, plus AI drafts still
     * awaiting review (so a pending draft isn't drafted again tomorrow). A
     * faculty member's own unpublished drafts don't count until published,
     * and rejected AI drafts don't count at all.
     */
    private function countable()
    {
        return Question::query()->where(fn ($q) => $q->where('is_active', true)
            ->orWhere(fn ($p) => $p->where('source', Question::SOURCE_AI_SUBSTITUTE)->where('review_status', Question::REVIEW_PENDING)));
    }

    /** @return array{live: int, pending: int} */
    private function questionCounts(array $topicIds): array
    {
        return [
            'live' => Question::whereIn('topic_id', $topicIds)->where('is_active', true)->count(),
            'pending' => Question::whereIn('topic_id', $topicIds)->where('is_active', false)->pendingAiReview()->count(),
        ];
    }

    private function subtree(Topic $topic, Collection $children): Collection
    {
        $all = collect([$topic]);
        foreach ($children->get($topic->id, collect()) as $child) {
            $all = $all->merge($this->subtree($child, $children));
        }

        return $all;
    }

    private function hasTargetedAncestor(Topic $topic, Collection $topics): bool
    {
        $seen = [];
        $parentId = $topic->parent_id;
        while ($parentId && ! isset($seen[$parentId]) && ($parent = $topics->get($parentId))) {
            if ((int) $parent->tos_items > 0) {
                return true;
            }
            $seen[$parentId] = true;
            $parentId = $parent->parent_id;
        }

        return false;
    }

    private function graceDays(): int
    {
        return max(1, (int) config('curriculum.gap_fill_grace_days', 14));
    }

    private function maxPerTopic(): int
    {
        return max(1, (int) config('curriculum.gap_fill_max_per_topic', 5));
    }

    // ── Notifications ───────────────────────────────────────────────────────

    //
    // Queued during a run and sent as ONE digest per person per kind, so a
    // faculty member with 40 short topics gets one notice, not 40.

    /** @var array<string, array{recipient: int, role: string, kind: string, items: array<int, array{topic: Topic, count: int, deadline: ?Carbon}>}> */
    private array $queue = [];

    private function notifyFirstNotice(Topic $topic, int $shortBy, Carbon $now): void
    {
        $faculty = $this->facultyIds($topic)->map(fn ($id) => [$id, 'faculty']);
        // Nobody assigned to the subject: tell the chair instead, or no one would know.
        $recipients = $faculty->isNotEmpty() ? $faculty : $this->chairIds()->map(fn ($id) => [$id, 'chair']);

        $this->enqueue('notice', $recipients, $topic, $shortBy, $now->copy()->addDays($this->graceDays()));
    }

    private function notifyFinalWarning(Topic $topic, int $shortBy, Carbon $deadline): void
    {
        $this->enqueue('warning', $this->allRecipients($topic), $topic, $shortBy, $deadline);
    }

    private function notifyDrafted(Topic $topic, int $drafted): void
    {
        $this->enqueue('drafted', $this->allRecipients($topic), $topic, $drafted, null);
    }

    private function allRecipients(Topic $topic): Collection
    {
        return $this->facultyIds($topic)->map(fn ($id) => [$id, 'faculty'])
            ->merge($this->chairIds()->map(fn ($id) => [$id, 'chair']));
    }

    /** @param  Collection<int, array{0: int, 1: string}>  $recipients  [user id, faculty|chair] */
    private function enqueue(string $kind, Collection $recipients, Topic $topic, int $count, ?Carbon $deadline): void
    {
        foreach ($recipients->unique(fn ($r) => $r[0]) as [$id, $role]) {
            $key = "{$kind}|{$id}";
            $this->queue[$key] ??= ['recipient' => (int) $id, 'role' => $role, 'kind' => $kind, 'items' => []];
            $this->queue[$key]['items'][] = ['topic' => $topic, 'count' => $count, 'deadline' => $deadline];
        }
    }

    private function flushNotifications(): void
    {
        $rows = [];
        foreach ($this->queue as $entry) {
            $items = collect($entry['items']);
            $total = $items->sum('count');
            $topics = $items->count();
            $list = $items->take(5)->map(fn ($i) => $this->topicRef($i['topic']) . " ({$i['count']})")->implode(', ')
                . ($topics > 5 ? ', and ' . ($topics - 5) . ' more' : '');
            $deadline = $items->pluck('deadline')->filter()->min();

            [$title, $message] = match ($entry['kind']) {
                'notice' => ['Test Bank needs questions',
                    "{$topics} topic(s) are short of their TOS item count by {$total} question(s) in all: {$list}. "
                    . 'Please add them by ' . $deadline->format('M j, Y') . ' — after that, AI will draft substitutes for review.'],
                'warning' => ['Test Bank deadline approaching',
                    "Final reminder: {$topics} topic(s) are still {$total} question(s) short of their TOS item count: {$list}. "
                    . 'AI will start drafting substitutes after ' . $deadline->format('M j, Y') . '.'],
                default => ['AI substitute questions need review',
                    "AI drafted {$total} substitute question(s) for {$topics} topic(s) that stayed short of their TOS item count: {$list}. "
                    . 'They stay hidden from students until approved — please review them.'],
            };

            $rows[] = [
                'recipient_id' => $entry['recipient'],
                'type' => 'test_bank_gap',
                'title' => $title,
                'message' => $message,
                'link' => $entry['role'] === 'chair' ? route('chair.ai-review') : route('faculty.test-bank.ai-review'),
                'is_read' => 0,
                'reference_type' => 'topic',
                'reference_id' => $items->first()['topic']->id,
                'created_at' => now(),
            ];
        }

        $this->queue = [];
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('notifications')->insert($chunk);
        }
    }

    private function facultyIds(Topic $topic): Collection
    {
        return DB::table('faculty_subjects')
            ->join('users', 'users.id', '=', 'faculty_subjects.faculty_id')
            ->where('faculty_subjects.subject_id', $topic->subject_id)
            ->where('users.is_active', true)
            ->pluck('faculty_subjects.faculty_id');
    }

    private function chairIds(): Collection
    {
        return User::where('role_id', Role::ADMIN)->where('is_active', true)->pluck('id');
    }

    private function topicRef(Topic $topic): string
    {
        return '"' . $topic->name . '"' . ($topic->subject ? " ({$topic->subject->code})" : '');
    }

    private function audit(Topic $topic, int $drafted): void
    {
        $version = $topic->curriculum_version_id ? CurriculumVersion::find($topic->curriculum_version_id) : null;
        if (! $version || ! Schema::hasTable('curriculum_audits')) {
            return;
        }

        CurriculumAuditor::record($version, null, CurriculumAudit::ACTION_AI_GAP_FILLED,
            "{$drafted} AI question(s) drafted for \"{$topic->name}\" (TOS target {$topic->tos_items}), pending review",
            $topic->subject_id);
    }
}
