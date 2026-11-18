<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Concerns\ManagesTopics;
use App\Http\Controllers\Concerns\SubjectTheme;
use App\Http\Controllers\Controller;
use App\Models\CurriculumImportBatch;
use App\Models\CurriculumVersion;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Topic;
use App\Support\BatchYear;
use App\Support\CurriculumScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SubjectManagementController extends Controller
{
    use SubjectTheme, ManagesTopics;

    /** Chair manages every subject's topics, so this hook is always open. */
    protected function canManageTopicSubject(Subject $subject): bool
    {
        return true;
    }

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
            'sections' => Schema::hasTable('sections')
                ? Section::where('is_active', true)->orderBy('name')->get(['name', 'year_level'])
                : collect(),
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

}
