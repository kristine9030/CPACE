<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Concerns\SubjectTheme;
use App\Http\Controllers\Controller;
use App\Models\CurriculumAudit;
use App\Models\CurriculumImportBatch;
use App\Models\CurriculumVersion;
use App\Models\Subject;
use App\Models\Topic;
use App\Support\BatchYear;
use App\Support\CurriculumAuditor;
use App\Support\CurriculumScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SubjectManagementController extends Controller
{
    use SubjectTheme;

    /**
     * The Subject & Curriculum page. Subjects are shared by every curriculum;
     * the topic trees belong to one curriculum version, chosen with
     * ?version=. With no choice the draft is shown when one is in progress
     * (that's where the chair is working), otherwise the active curriculum.
     *
     * Subjects show as folders; ?subject= opens one to its faculty and topics.
     */
    public function index(Request $request)
    {
        $versions = CurriculumScope::enabled()
            ? CurriculumVersion::with('creator')->orderByDesc('id')->get()
            : collect();

        $version = $versions->firstWhere('id', (int) $request->query('version'))
            ?? $versions->firstWhere('status', CurriculumVersion::STATUS_DRAFT)
            ?? $versions->firstWhere('status', CurriculumVersion::STATUS_ACTIVE);

        $versionId = $version?->id;

        $subjects = Subject::with([
            'faculty' => fn ($query) => $query->orderBy('first_name'),
            'topics' => fn ($query) => $query->inCurriculum($versionId)->withCount('questions')->orderBy('sort_order')->orderBy('name'),
        ])->orderBy('id')->get();

        // Only the opened subject shows its topic tree; the cards just count.
        $openSubject = $request->filled('subject') ? $subjects->firstWhere('id', (int) $request->query('subject')) : null;
        $openSubject?->setRelation('topicTree', Topic::buildTree($openSubject->topics));

        // Where this curriculum's topics came from: the TOS file last imported
        // into it, and an import still waiting for the chair's review.
        $imports = $version && Schema::hasTable('curriculum_import_batches')
            ? CurriculumImportBatch::where('curriculum_version_id', $version->id)->orderByDesc('id')->get()
            : collect();

        return view('chair.subjects', [
            'subjects' => $subjects,
            'pendingImport' => $imports->firstWhere('status', CurriculumImportBatch::STATUS_PENDING),
            'lastImport' => $imports->firstWhere('status', CurriculumImportBatch::STATUS_COMMITTED),
            'openSubject' => $openSubject,
            'looks' => $subjects->mapWithKeys(fn (Subject $subject) => [$subject->id => $this->subjectLook($subject)]),
            'versions' => $versions,
            'version' => $version,
            'readOnly' => $version !== null && ! $version->isEditable(),
            'audits' => $version ? $version->audits()->with(['user', 'subject'])->limit(15)->get() : collect(),
            'suggestedBatch' => $this->suggestedFirstBatch($versions),
        ]);
    }

    /**
     * Folder colour + icon. The six CPALE subjects use the same colours as
     * their Mock Exam folders; any other subject uses the colour set on it.
     *
     * @return array{base:string,dark:string,icon:string}
     */
    private function subjectLook(Subject $subject): array
    {
        $code = strtoupper((string) $subject->code);
        $custom = preg_match('/^#[0-9a-f]{6}$/i', (string) $subject->color) ? $subject->color : null;
        $theme = isset(self::SUBJECT_COLORS[$code]) || ! $custom
            ? self::theme($code)
            : ['base' => $custom, 'dark' => self::darken($custom, 0.32)];

        return ['base' => $theme['base'], 'dark' => $theme['dark'], 'icon' => self::subjectIcon($code)];
    }

    public function storeSubject(Request $request)
    {
        $data = $this->validateSubject($request);
        $data['code'] = strtoupper($data['code']);
        Subject::create($data);

        return back()->with('status', "{$data['code']} was added successfully.");
    }

    public function updateSubject(Request $request, Subject $subject)
    {
        $data = $this->validateSubject($request, $subject);
        $data['code'] = strtoupper($data['code']);
        $subject->update($data);

        return back()->with('status', "{$subject->code} was updated successfully.");
    }

    public function destroySubject(Subject $subject)
    {
        if ($subject->topics()->exists() || $subject->faculty()->exists()) {
            return back()->with('error', 'This subject cannot be removed while it has topics or assigned faculty. Remove those links first, or mark the subject inactive.');
        }

        $code = $subject->code;
        $subject->delete();

        return back()->with('status', "{$code} was removed.");
    }

    public function storeTopic(Request $request, Subject $subject)
    {
        $version = $this->targetVersion($request);
        if ($version && ! $version->isEditable()) {
            return back()->with('error', self::ARCHIVED_MESSAGE);
        }

        $data = $this->validateTopic($request, $subject, $version?->id);
        $topic = $subject->topics()->create($version ? $data + ['curriculum_version_id' => $version->id] : $data);

        $this->audit($topic, CurriculumAudit::ACTION_TOPIC_ADDED, $topic->name);

        return back()->with('status', "Topic “{$data['name']}” was added to {$subject->code}.");
    }

    public function updateTopic(Request $request, Subject $subject, Topic $topic)
    {
        abort_unless($topic->subject_id === $subject->id, 404);
        if ($this->isArchived($topic)) {
            return back()->with('error', self::ARCHIVED_MESSAGE);
        }

        $data = $this->validateTopic($request, $subject, $topic->curriculum_version_id, $topic);

        $siblings = $subject->topics()->inCurriculum($topic->curriculum_version_id)->get();
        if (! empty($data['parent_id']) && $topic->isSelfOrDescendant((int) $data['parent_id'], $siblings)) {
            return back()->withInput()->withErrors(['parent_id' => 'A topic cannot be moved under itself or one of its own subtopics.']);
        }

        $topic->update($data);
        $this->audit($topic, CurriculumAudit::ACTION_TOPIC_EDITED, $topic->name);

        return back()->with('status', "Topic “{$topic->name}” was updated.");
    }

    public function toggleTopic(Subject $subject, Topic $topic)
    {
        abort_unless($topic->subject_id === $subject->id, 404);
        if ($this->isArchived($topic)) {
            return back()->with('error', self::ARCHIVED_MESSAGE);
        }

        $topic->update(['is_active' => !$topic->is_active]);

        $status = $topic->is_active ? 'enabled' : 'disabled';
        return back()->with('status', "Topic “{$topic->name}” has been {$status}.");
    }

    public function destroyTopic(Subject $subject, Topic $topic)
    {
        abort_unless($topic->subject_id === $subject->id, 404);
        if ($this->isArchived($topic)) {
            return back()->with('error', self::ARCHIVED_MESSAGE);
        }

        if ($topic->questions()->exists()) {
            return back()->with('error', 'This topic contains test-bank questions and cannot be removed. Edit it and mark it inactive instead.');
        }

        if ($topic->children()->exists()) {
            return back()->with('error', 'This topic has subtopics and cannot be removed. Remove or reassign its subtopics first.');
        }

        $name = $topic->name;
        $this->audit($topic, CurriculumAudit::ACTION_TOPIC_REMOVED, $name);
        $topic->delete();

        return back()->with('status', "Topic “{$name}” was removed from {$subject->code}.");
    }

    /**
     * Default first batch for a new curriculum: next school year's batch, but
     * never at or before the current curriculum's first batch (the new one
     * must take over from a later batch).
     */
    private function suggestedFirstBatch($versions): string
    {
        $suggested = BatchYear::forDate(now()->addYear());
        $activeFrom = $versions->firstWhere('status', CurriculumVersion::STATUS_ACTIVE)?->effective_from_batch;

        if ($activeFrom && $suggested <= $activeFrom) {
            $suggested = BatchYear::next($activeFrom) ?? $suggested;
        }

        return $suggested;
    }

    private const ARCHIVED_MESSAGE = 'This curriculum is archived and kept as read-only history, so its topics can no longer be changed.';

    /**
     * The curriculum a new topic goes into: the version the chair is viewing
     * (posted by the form), falling back to the active one.
     */
    private function targetVersion(Request $request): ?CurriculumVersion
    {
        if (! CurriculumScope::enabled()) {
            return null;
        }

        $id = (int) $request->input('curriculum_version_id') ?: CurriculumScope::activeId();

        return $id ? CurriculumVersion::find($id) : null;
    }

    private function isArchived(Topic $topic): bool
    {
        return CurriculumScope::enabled()
            && $topic->curriculum_version_id !== null
            && CurriculumVersion::whereKey($topic->curriculum_version_id)->where('status', CurriculumVersion::STATUS_ARCHIVED)->exists();
    }

    private function audit(Topic $topic, string $action, string $details): void
    {
        if (! CurriculumScope::enabled() || $topic->curriculum_version_id === null) {
            return;
        }

        $version = CurriculumVersion::find($topic->curriculum_version_id);
        if ($version) {
            CurriculumAuditor::record($version, Auth::user(), $action, $details, $topic->subject_id);
        }
    }

    private function validateSubject(Request $request, ?Subject $subject = null): array
    {
        return $request->validate([
            'code' => [
                'required', 'string', 'max:20', 'regex:/^[A-Za-z0-9-]+$/',
                Rule::unique('subjects', 'code')->ignore($subject?->id),
            ],
            'name'              => ['required', 'string', 'max:255'],
            'description'       => ['nullable', 'string', 'max:2000'],
            'passing_threshold' => ['required', 'integer', 'between:1,100'],
            'color'             => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active'         => ['required', 'boolean'],
        ]);
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
        ]);
    }
}
