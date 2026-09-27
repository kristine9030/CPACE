<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Controller;
use App\Models\CurriculumAudit;
use App\Models\CurriculumImportBatch;
use App\Models\CurriculumImportItem;
use App\Models\CurriculumVersion;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\CurriculumTosParser;
use App\Support\CurriculumAuditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Import a PRC Table of Specifications PDF into a curriculum version.
 *
 * Upload -> parse every subject found in the PDF -> stage the outline ->
 * the chair reviews it (rename, include/exclude, choose which subjects) ->
 * commit creates the topics. Nothing touches `topics` before the commit.
 */
class CurriculumImportController extends Controller
{
    public function store(Request $request, CurriculumTosParser $parser)
    {
        $data = $request->validate([
            'curriculum_version_id' => ['required', 'integer'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ], [
            'file.mimes' => 'Upload the Table of Specifications as a PDF file.',
        ]);

        $version = CurriculumVersion::findOrFail($data['curriculum_version_id']);
        if (! $version->isEditable()) {
            return back()->with('error', 'An archived curriculum is read-only; import into the current curriculum or a draft instead.');
        }

        try {
            $outline = $parser->parseFile($request->file('file')->getRealPath());
        } catch (\Throwable $e) {
            Log::warning('TOS import could not read the PDF.', ['error' => $e->getMessage()]);

            return back()->with('error', 'That PDF could not be read. Make sure it is the official PRC Table of Specifications (a text PDF, not a scanned image).');
        }

        $subjects = Subject::all(['id', 'code']);
        $outline = collect($outline)->mapWithKeys(fn ($items, $code) => [
            optional($subjects->first(fn ($s) => strtoupper($s->code) === $code))->id => $items,
        ])->filter(fn ($items, $subjectId) => $subjectId !== '' && $subjectId !== null);

        if ($outline->isEmpty()) {
            return back()->with('error', 'No CPALE subject sections were found in that PDF. This importer reads the official PRC Table of Specifications layout ("Table of Specifications in ...").');
        }

        $batch = DB::transaction(function () use ($version, $request, $outline) {
            $batch = CurriculumImportBatch::create([
                'curriculum_version_id' => $version->id,
                'created_by' => Auth::id(),
                'original_filename' => $request->file('file')->getClientOriginalName(),
                'status' => CurriculumImportBatch::STATUS_PENDING,
            ]);

            $order = 0;
            foreach ($outline as $subjectId => $items) {
                $ids = [];
                foreach ($items as $index => $item) {
                    $ids[$index] = CurriculumImportItem::create([
                        'batch_id' => $batch->id,
                        'subject_id' => $subjectId,
                        'parent_item_id' => $item['parent'] !== null ? ($ids[$item['parent']] ?? null) : null,
                        'ref' => mb_substr($item['ref'], 0, 30),
                        'name' => $item['name'],
                        'full_name' => $item['full_name'],
                        'depth' => min($item['depth'], 255),
                        'weight_percent' => $item['weight'],
                        'item_count' => $item['items'],
                        'sort_order' => $order++,
                        'included' => true,
                    ])->id;
                }
            }

            return $batch;
        });

        return redirect()->route('chair.curriculum.import.review', $batch);
    }

    public function review(CurriculumImportBatch $batch)
    {
        if ($batch->status === CurriculumImportBatch::STATUS_COMMITTED) {
            return redirect()->route('chair.subjects', ['version' => $batch->curriculum_version_id])
                ->with('status', 'This import was already added to the curriculum.');
        }

        $batch->load('items', 'curriculumVersion');
        $subjects = Subject::whereIn('id', $batch->items->pluck('subject_id')->unique())->orderBy('id')->get();

        $existing = Topic::where('curriculum_version_id', $batch->curriculum_version_id)
            ->selectRaw('subject_id, COUNT(*) as total')
            ->groupBy('subject_id')
            ->pluck('total', 'subject_id');

        return view('chair.curriculum-import-review', [
            'batch' => $batch,
            'version' => $batch->curriculumVersion,
            'subjects' => $subjects,
            'itemsBySubject' => $batch->items->groupBy('subject_id'),
            'existingTopics' => $existing,
        ]);
    }

    /**
     * Create the reviewed topics. The review page posts one JSON payload:
     * {subjects: [ids to import], items: {itemId: {include, name}}}.
     */
    public function commit(Request $request, CurriculumImportBatch $batch)
    {
        abort_if($batch->status === CurriculumImportBatch::STATUS_COMMITTED, 422, 'This import was already committed.');

        $version = CurriculumVersion::findOrFail($batch->curriculum_version_id);
        if (! $version->isEditable()) {
            return redirect()->route('chair.subjects')->with('error', 'That curriculum has since been archived, so it can no longer be changed.');
        }

        $payload = json_decode((string) $request->input('payload'), true) ?: [];
        $subjectIds = collect($payload['subjects'] ?? [])->map(fn ($id) => (int) $id)->all();
        $edits = $payload['items'] ?? [];

        if ($subjectIds === []) {
            return back()->with('error', 'Choose at least one subject to import.');
        }

        $items = $batch->items()->whereIn('subject_id', $subjectIds)->get();
        $created = [];

        DB::transaction(function () use ($items, $edits, $version, &$created) {
            $topicFor = [];   // import item id => topic id

            foreach ($items as $item) {
                $edit = $edits[$item->id] ?? [];
                if (array_key_exists('include', $edit) ? ! $edit['include'] : ! $item->included) {
                    continue;
                }

                $fullName = trim((string) ($edit['name'] ?? $item->name));
                if ($fullName === '') {
                    continue;
                }
                $name = mb_substr($fullName, 0, 150);

                // Attach to the nearest included ancestor (an excluded level is skipped over).
                $parentId = null;
                $cursor = $item->parent_item_id;
                while ($cursor !== null) {
                    if (isset($topicFor[$cursor])) {
                        $parentId = $topicFor[$cursor];
                        break;
                    }
                    $cursor = $items->firstWhere('id', $cursor)?->parent_item_id;
                }

                // Re-importing merges: an existing topic with the same name under
                // the same parent is reused rather than duplicated.
                $topic = Topic::where('subject_id', $item->subject_id)
                    ->where('curriculum_version_id', $version->id)
                    ->where('parent_id', $parentId)
                    ->where('name', $name)
                    ->first();

                if (! $topic) {
                    $topic = Topic::create([
                        'subject_id' => $item->subject_id,
                        'curriculum_version_id' => $version->id,
                        'parent_id' => $parentId,
                        'name' => $name,
                        'description' => $item->full_name && mb_strlen($item->full_name) > 150 ? $item->full_name : null,
                        'sort_order' => $item->sort_order,
                        'is_active' => true,
                        'tos_weight' => $item->weight_percent,
                        'tos_items' => $item->item_count,
                    ]);
                    $created[$item->subject_id] = ($created[$item->subject_id] ?? 0) + 1;
                }

                $topicFor[$item->id] = $topic->id;
            }
        });

        $batch->update(['status' => CurriculumImportBatch::STATUS_COMMITTED]);

        $subjects = Subject::whereIn('id', array_keys($created))->pluck('code', 'id');
        foreach ($created as $subjectId => $count) {
            CurriculumAuditor::record($version, Auth::user(), CurriculumAudit::ACTION_TOPICS_IMPORTED,
                "{$count} topic(s) from {$batch->original_filename}", $subjectId);
        }
        $this->notifyFaculty($version, array_keys($created), $subjects);

        $total = array_sum($created);
        $summary = $total > 0
            ? "Imported {$total} topic(s) into " . $subjects->implode(', ') . '.'
            : 'No new topics were added — everything selected already existed in this curriculum.';

        return redirect()->route('chair.subjects', ['version' => $version->id])->with('status', $summary);
    }

    public function destroy(CurriculumImportBatch $batch)
    {
        $versionId = $batch->curriculum_version_id;
        $batch->items()->delete();
        $batch->delete();

        return redirect()->route('chair.subjects', ['version' => $versionId])->with('status', 'The import was discarded. No topics were changed.');
    }

    /** Faculty of each subject that received topics: review your questions against them. */
    private function notifyFaculty(CurriculumVersion $version, array $subjectIds, $codes): void
    {
        $assignments = DB::table('faculty_subjects')->whereIn('subject_id', $subjectIds)->get(['faculty_id', 'subject_id']);
        if ($assignments->isEmpty()) {
            return;
        }

        $where = $version->isDraft() ? 'the draft curriculum "' . $version->label . '"' : 'the current curriculum';

        DB::table('notifications')->insert($assignments->groupBy('faculty_id')->map(fn ($rows, $facultyId) => [
            'recipient_id' => $facultyId,
            'type' => 'curriculum_update',
            'title' => 'Curriculum topics updated',
            'message' => 'New topics were imported for ' . $rows->map(fn ($r) => $codes[$r->subject_id] ?? '')->filter()->implode(', ')
                . " in {$where}. Review your Test Bank questions against them.",
            'link' => route('faculty.test-bank'),
            'is_read' => 0,
            'reference_type' => 'curriculum_version',
            'reference_id' => $version->id,
            'created_at' => now(),
        ])->values()->all());
    }
}
