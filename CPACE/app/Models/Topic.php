<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    public $timestamps = false;

    protected $fillable = ['subject_id', 'parent_id', 'name', 'description', 'sort_order', 'is_active'];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function parent()
    {
        return $this->belongsTo(Topic::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Topic::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    /**
     * Nest a flat collection of topics (already loaded for one subject) into a
     * parent/children tree in memory, since Eloquent can't eager-load a
     * self-relation to unbounded depth.
     */
    public static function buildTree(Collection $flat, ?int $parentId = null): Collection
    {
        return $flat->where('parent_id', $parentId)->map(function (Topic $topic) use ($flat) {
            $topic->setRelation('children', static::buildTree($flat, $topic->id));

            return $topic;
        })->values();
    }

    /**
     * True if $candidateId is this topic itself or one of its descendants —
     * used to stop a topic being reparented into its own subtree.
     */
    public function isSelfOrDescendant(int $candidateId, Collection $flatSubjectTopics): bool
    {
        if ($candidateId === $this->id) {
            return true;
        }

        return static::buildTree($flatSubjectTopics, $this->id)
            ->flatMap(fn (Topic $t) => static::flatten($t))
            ->contains('id', $candidateId);
    }

    private static function flatten(Topic $topic): Collection
    {
        return collect([$topic])->merge($topic->children->flatMap(fn (Topic $t) => static::flatten($t)));
    }

    /**
     * Roll a student's performance_records (keyed by topic_id) up through a
     * topic tree: each node gets progress_attempts/progress_correct/
     * progress_accuracy summed across itself and all of its descendants, so
     * a parent topic reflects activity on its subtopics even when it has no
     * questions of its own. Returns [attempts, correct] for the caller's own
     * rollup one level up.
     */
    public static function attachProgress(Collection $nodes, \Illuminate\Support\Collection $performanceByTopicId): array
    {
        $totalAttempts = 0;
        $totalCorrect = 0;

        foreach ($nodes as $node) {
            $own = $performanceByTopicId->get($node->id);
            $ownAttempts = $own->total_attempts ?? 0;
            $ownCorrect = $own->correct_count ?? 0;

            [$childAttempts, $childCorrect] = static::attachProgress($node->children, $performanceByTopicId);

            $attempts = $ownAttempts + $childAttempts;
            $correct = $ownCorrect + $childCorrect;

            $node->setAttribute('progress_attempts', $attempts);
            $node->setAttribute('progress_correct', $correct);
            $node->setAttribute('progress_accuracy', $attempts > 0 ? (int) round($correct / $attempts * 100) : null);

            $totalAttempts += $attempts;
            $totalCorrect += $correct;
        }

        return [$totalAttempts, $totalCorrect];
    }
}
