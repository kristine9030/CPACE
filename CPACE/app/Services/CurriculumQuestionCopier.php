<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Topic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Copies a subject's Test Bank questions from one curriculum version into
 * another, so a new curriculum starts with the questions that still apply
 * instead of an empty bank. Faculty then delete what no longer fits.
 *
 * Topics are matched by their path ("Inventories > Estimating procedures"),
 * because leaf names repeat across the tree ("Nature" sits under several
 * parents). When the path changed but the name is unique on both sides, the
 * name alone is used. Unmatched topics simply get nothing.
 *
 * Copies are new rows; the originals stay attached to the old curriculum so
 * its history (and every student answer recorded against them) is untouched.
 * Re-running is safe: a question whose text already exists in the target
 * topic is skipped.
 */
class CurriculumQuestionCopier
{
    /**
     * @return array{questions: int, topics: int, unmatched: int}
     */
    public function copy(int $subjectId, int $fromVersionId, int $toVersionId): array
    {
        $from = $this->topicsOf($subjectId, $fromVersionId);
        $to = $this->topicsOf($subjectId, $toVersionId);

        $pairs = $this->match($from, $to);

        $copiedQuestions = 0;
        $topicsFilled = 0;

        DB::transaction(function () use ($pairs, &$copiedQuestions, &$topicsFilled) {
            foreach ($pairs as $fromTopicId => $toTopicId) {
                $existing = Question::where('topic_id', $toTopicId)->pluck('question_text')
                    ->map(fn ($text) => $this->normalize($text))
                    ->flip();

                $sources = Question::with(['choices', 'variants'])->where('topic_id', $fromTopicId)->orderBy('id')->get();
                $copiedHere = 0;

                foreach ($sources as $source) {
                    if ($existing->has($this->normalize($source->question_text))) {
                        continue;
                    }

                    $copy = new Question(\App\Support\QuestionExhibit::attributesOf($source) + [
                        'topic_id' => $toTopicId,
                        'created_by' => $source->created_by,
                        'question_text' => $source->question_text,
                        'question_type' => $source->question_type,
                        'difficulty' => $source->difficulty,
                        'explanation' => $source->explanation,
                        'is_active' => $source->is_active,
                    ]);
                    // Keep the original authoring date, so bank-growth charts
                    // don't show every copied question as written today.
                    $copy->created_at = $source->created_at;
                    $copy->save();

                    foreach ($source->choices as $choice) {
                        $copy->choices()->create([
                            'choice_label' => $choice->choice_label,
                            'choice_text' => $choice->choice_text,
                            'is_correct' => $choice->is_correct,
                        ]);
                    }
                    foreach ($source->variants as $variant) {
                        $copy->variants()->create([
                            'variant_text' => $variant->variant_text,
                            'source' => $variant->source,
                            'is_active' => $variant->is_active,
                        ]);
                    }

                    $copiedHere++;
                }

                $copiedQuestions += $copiedHere;
                if ($copiedHere > 0) {
                    $topicsFilled++;
                }
            }
        });

        $withQuestions = $from->filter(fn ($t) => $t->questions_count > 0)->pluck('id');

        return [
            'questions' => $copiedQuestions,
            'topics' => $topicsFilled,
            'unmatched' => $withQuestions->reject(fn ($id) => isset($pairs[$id]))->count(),
        ];
    }

    /** @return Collection<int, Topic> */
    private function topicsOf(int $subjectId, int $versionId): Collection
    {
        return Topic::where('subject_id', $subjectId)
            ->where('curriculum_version_id', $versionId)
            ->withCount('questions')
            ->get(['id', 'parent_id', 'name']);
    }

    /**
     * Old topic id => new topic id.
     *
     * @return array<int, int>
     */
    private function match(Collection $from, Collection $to): array
    {
        $fromPaths = $this->paths($from);
        $toByPath = array_flip($this->paths($to));

        $fromNameCounts = $from->countBy(fn ($t) => $this->normalize($t->name));
        $toByName = $to->groupBy(fn ($t) => $this->normalize($t->name));

        $pairs = [];
        foreach ($from as $topic) {
            $path = $fromPaths[$topic->id];
            if (isset($toByPath[$path])) {
                $pairs[$topic->id] = (int) $toByPath[$path];
                continue;
            }

            $name = $this->normalize($topic->name);
            $candidates = $toByName->get($name);
            if (($fromNameCounts[$name] ?? 0) === 1 && $candidates && $candidates->count() === 1) {
                $pairs[$topic->id] = (int) $candidates->first()->id;
            }
        }

        return $pairs;
    }

    /** @return array<int, string> topic id => "parent > child" path */
    private function paths(Collection $topics): array
    {
        $byId = $topics->keyBy('id');
        $paths = [];

        foreach ($topics as $topic) {
            $parts = [];
            $node = $topic;
            $hops = 0;
            while ($node && $hops < 12) {
                array_unshift($parts, $this->normalize($node->name));
                $node = $node->parent_id ? $byId->get($node->parent_id) : null;
                $hops++;
            }
            $paths[$topic->id] = implode(' > ', $parts);
        }

        return $paths;
    }

    private function normalize(?string $text): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $text)));
    }
}
