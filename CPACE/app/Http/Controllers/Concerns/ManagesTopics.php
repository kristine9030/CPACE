<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CurriculumAudit;
use App\Models\CurriculumVersion;
use App\Models\Subject;
use App\Models\Topic;
use App\Support\Auditor;
use App\Support\CurriculumAuditor;
use App\Support\CurriculumScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Topic CRUD shared by the Chair (every subject) and Faculty (their assigned
 * subjects only) — same forms, same validation, same curriculum/audience
 * rules either way. Implementing controllers only need to say who is allowed
 * to manage a given subject's topics via canManageTopicSubject().
 */
trait ManagesTopics
{
    private const TOPIC_NOT_ASSIGNED_MESSAGE = "You're not assigned to this subject, so you can't manage its topics. Ask your Program Chair for access if you think this is a mistake.";

    private const TOPIC_ARCHIVED_MESSAGE = 'This curriculum is archived and kept as read-only history, so its topics can no longer be changed.';

    abstract protected function canManageTopicSubject(Subject $subject): bool;

    public function storeTopic(Request $request, Subject $subject)
    {
        abort_unless($this->canManageTopicSubject($subject), 403, self::TOPIC_NOT_ASSIGNED_MESSAGE);

        $version = $this->topicTargetVersion($request);
        if ($version && ! $version->isEditable()) {
            return back()->with('error', self::TOPIC_ARCHIVED_MESSAGE);
        }

        $data = $this->normalizeTopicAudience($this->validateTopic($request, $subject, $version?->id));
        $topic = $subject->topics()->create($version ? $data + ['curriculum_version_id' => $version->id] : $data);

        $this->auditTopic($topic, CurriculumAudit::ACTION_TOPIC_ADDED, $topic->name);
        Auditor::log(Auth::user(), 'topic_added', "Added topic \"{$topic->name}\" to {$subject->code}.", 'Topic', $topic->id);

        return back()->with('status', "Topic “{$data['name']}” was added to {$subject->code}.");
    }

    public function updateTopic(Request $request, Subject $subject, Topic $topic)
    {
        abort_unless($this->canManageTopicSubject($subject), 403, self::TOPIC_NOT_ASSIGNED_MESSAGE);
        abort_unless($topic->subject_id === $subject->id, 404);
        if ($this->isTopicArchived($topic)) {
            return back()->with('error', self::TOPIC_ARCHIVED_MESSAGE);
        }

        $data = $this->normalizeTopicAudience($this->validateTopic($request, $subject, $topic->curriculum_version_id, $topic));

        $siblings = $subject->topics()->inCurriculum($topic->curriculum_version_id)->get();
        if (! empty($data['parent_id']) && $topic->isSelfOrDescendant((int) $data['parent_id'], $siblings)) {
            return back()->withInput()->withErrors(['parent_id' => 'A topic cannot be moved under itself or one of its own subtopics.']);
        }

        $topic->update($data);
        $this->auditTopic($topic, CurriculumAudit::ACTION_TOPIC_EDITED, $topic->name);
        Auditor::log(Auth::user(), 'topic_edited', "Edited topic \"{$topic->name}\" in {$subject->code}.", 'Topic', $topic->id);

        return back()->with('status', "Topic “{$topic->name}” was updated.");
    }

    public function toggleTopic(Subject $subject, Topic $topic)
    {
        abort_unless($this->canManageTopicSubject($subject), 403, self::TOPIC_NOT_ASSIGNED_MESSAGE);
        abort_unless($topic->subject_id === $subject->id, 404);
        if ($this->isTopicArchived($topic)) {
            return back()->with('error', self::TOPIC_ARCHIVED_MESSAGE);
        }

        $topic->update(['is_active' => ! $topic->is_active]);

        $status = $topic->is_active ? 'enabled' : 'disabled';

        return back()->with('status', "Topic “{$topic->name}” has been {$status}.");
    }

    public function destroyTopic(Subject $subject, Topic $topic)
    {
        abort_unless($this->canManageTopicSubject($subject), 403, self::TOPIC_NOT_ASSIGNED_MESSAGE);
        abort_unless($topic->subject_id === $subject->id, 404);
        if ($this->isTopicArchived($topic)) {
            return back()->with('error', self::TOPIC_ARCHIVED_MESSAGE);
        }

        if ($topic->questions()->exists()) {
            return back()->with('error', 'This topic contains test-bank questions and cannot be removed. Edit it and mark it inactive instead.');
        }

        if ($topic->children()->exists()) {
            return back()->with('error', 'This topic has subtopics and cannot be removed. Remove or reassign its subtopics first.');
        }

        $name = $topic->name;
        $this->auditTopic($topic, CurriculumAudit::ACTION_TOPIC_REMOVED, $name);
        Auditor::log(Auth::user(), 'topic_removed', "Removed topic \"{$name}\" from {$subject->code}.", 'Topic', $topic->id);
        $topic->delete();

        return back()->with('status', "Topic “{$name}” was removed from {$subject->code}.");
    }

    public function bulkDestroyTopics(Request $request, Subject $subject)
    {
        abort_unless($this->canManageTopicSubject($subject), 403, self::TOPIC_NOT_ASSIGNED_MESSAGE);

        $ids = collect($request->input('topic_ids', []))->map(fn ($id) => (int) $id)->filter()->unique();
        if ($ids->isEmpty()) {
            return back()->with('error', 'No topics were selected.');
        }

        $pending = $subject->topics()->whereIn('id', $ids)->get()->keyBy('id');

        $removed = [];
        $skipped = [];

        // Deletes in passes so a selected subtopic clears the way for its
        // selected parent in the same request, regardless of selection order.
        do {
            $progress = false;
            foreach ($pending as $id => $topic) {
                if ($this->isTopicArchived($topic) || $topic->questions()->exists()) {
                    $skipped[] = $topic->name;
                    $pending->forget($id);
                    $progress = true;
                    continue;
                }
                if ($topic->children()->exists()) {
                    continue;
                }

                $this->auditTopic($topic, CurriculumAudit::ACTION_TOPIC_REMOVED, $topic->name);
                $topic->delete();
                $removed[] = $topic->name;
                $pending->forget($id);
                $progress = true;
            }
        } while ($progress && $pending->isNotEmpty());

        foreach ($pending as $topic) {
            $skipped[] = $topic->name;
        }

        if (empty($removed)) {
            return back()->with('error', 'None of the selected topics could be removed — they contain test-bank questions or subtopics, or belong to an archived curriculum.');
        }

        $message = count($removed) . ' topic' . (count($removed) === 1 ? '' : 's') . ' removed from ' . $subject->code . '.';
        if (! empty($skipped)) {
            $message .= ' ' . count($skipped) . ' skipped (contains questions, subtopics, or archived): ' . implode(', ', $skipped) . '.';
        }

        Auditor::log(Auth::user(), 'topic_removed', 'Bulk removed ' . count($removed) . " topic(s) from {$subject->code}: " . implode(', ', $removed) . '.', 'Subject', $subject->id);

        return back()->with('status', $message);
    }

    /**
     * The curriculum a new topic goes into: the version the caller is
     * viewing (posted by the form), falling back to the active one.
     */
    private function topicTargetVersion(Request $request): ?CurriculumVersion
    {
        if (! CurriculumScope::enabled()) {
            return null;
        }

        $id = (int) $request->input('curriculum_version_id') ?: CurriculumScope::activeId();

        return $id ? CurriculumVersion::find($id) : null;
    }

    private function isTopicArchived(Topic $topic): bool
    {
        return CurriculumScope::enabled()
            && $topic->curriculum_version_id !== null
            && CurriculumVersion::whereKey($topic->curriculum_version_id)->where('status', CurriculumVersion::STATUS_ARCHIVED)->exists();
    }

    private function auditTopic(Topic $topic, string $action, string $details): void
    {
        if (! CurriculumScope::enabled() || $topic->curriculum_version_id === null) {
            return;
        }

        $version = CurriculumVersion::find($topic->curriculum_version_id);
        if ($version) {
            CurriculumAuditor::record($version, Auth::user(), $action, $details, $topic->subject_id);
        }
    }

    /**
     * Names are unique among siblings (same parent) within a subject in ONE
     * curriculum version: a new curriculum reuses the same names, and the TOS
     * itself repeats leaf names under different parents ("Accounting for
     * SMEs"). A parent must be in the same subject and version.
     */
    private function validateTopic(Request $request, Subject $subject, ?int $versionId, ?Topic $topic = null): array
    {
        $sameCurriculum = function ($query) use ($subject, $versionId) {
            $query->where('subject_id', $subject->id);
            if ($versionId !== null) {
                $query->where('curriculum_version_id', $versionId);
            }
        };
        $parentId = $request->filled('parent_id') ? (int) $request->input('parent_id') : null;
        $sameParent = function ($query) use ($sameCurriculum, $parentId) {
            $sameCurriculum($query);
            $parentId === null ? $query->whereNull('parent_id') : $query->where('parent_id', $parentId);
        };

        return $request->validate([
            'name' => [
                'required', 'string', 'max:150',
                Rule::unique('topics', 'name')->where($sameParent)->ignore($topic?->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'parent_id'    => ['nullable', 'integer', Rule::exists('topics', 'id')->where($sameCurriculum)],
            'sort_order'   => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active'    => ['required', 'boolean'],
            'audience_years' => ['nullable', 'array'],
            'audience_years.*' => ['integer', 'between:1,6'],
            'audience_sections' => ['nullable', 'array'],
            'audience_sections.*' => ['string', 'max:100'],
            'audience_semesters' => ['nullable', 'array'],
            'audience_semesters.*' => ['integer', 'in:1,2'],
        ]);
    }

    /**
     * Pull the audience arrays out of already-validated data, normalized the
     * same way MockExamReviewController does (unique, sorted, empty -> null
     * so "no selection" reads as unrestricted rather than an empty JSON array).
     */
    private function normalizeTopicAudience(array $data): array
    {
        $data['audience_years'] = collect($data['audience_years'] ?? [])->map(fn ($y) => (int) $y)->unique()->sort()->values()->all() ?: null;
        $data['audience_sections'] = collect($data['audience_sections'] ?? [])->unique()->sort()->values()->all() ?: null;
        $data['audience_semesters'] = collect($data['audience_semesters'] ?? [])->map(fn ($s) => (int) $s)->unique()->sort()->values()->all() ?: null;

        return $data;
    }
}
