<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionImportBatch;
use App\Models\QuestionImportItem;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\AiQuestionImportService;
use App\Services\QuestionImportParser;
use App\Services\QuestionImportTemplateGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * "Import questions from a file" flow: upload a PDF/Word/Excel/CSV/image ->
 * parse it (rule-based first, AI fallback for messy text or any image) into
 * a staging batch -> faculty reviews/edits/approves each row -> commit()
 * copies the approved rows into the real Test Bank (questions/question_choices).
 * Nothing reaches the Test Bank until the faculty explicitly approves it.
 */
class QuestionImportController extends Controller
{
    private const ALLOWED_EXTENSIONS = ['pdf', 'docx', 'doc', 'xlsx', 'xls', 'csv', 'txt', 'jpg', 'jpeg', 'png', 'webp'];
    private const MAX_UPLOAD_KB = 20480; // 20 MB
    private const NOT_ASSIGNED_MESSAGE = "You're not assigned to this subject, so you can't import questions into it. Ask your Program Chair for access if you think this is a mistake.";

    /** Upload form. */
    public function create()
    {
        return view('faculty.test-bank-import', [
            'subjects'      => $this->subjectsFor(Auth::user())->orderBy('id')->get(),
            'recentBatches' => QuestionImportBatch::with('subject')
                ->where('faculty_id', Auth::id())
                ->latest()
                ->limit(6)
                ->get(),
        ]);
    }

    /**
     * Handle the upload: extract, parse (rule-based, then AI if needed),
     * stage the result, and send the faculty straight to the review screen.
     */
    public function store(Request $request, QuestionImportParser $parser, AiQuestionImportService $ai)
    {
        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'file'       => 'required|file|max:' . self::MAX_UPLOAD_KB
                . '|mimes:' . implode(',', self::ALLOWED_EXTENSIONS),
        ]);

        if (! $this->canManageSubject(Auth::user(), (int) $data['subject_id'])) {
            return redirect()->route('faculty.test-bank')->with('warning', self::NOT_ASSIGNED_MESSAGE);
        }

        /** @var UploadedFile $file */
        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        $batch = QuestionImportBatch::create([
            'faculty_id'         => Auth::id(),
            'subject_id'         => $data['subject_id'],
            'original_filename'  => $file->getClientOriginalName(),
            'file_type'          => $extension,
            'status'             => QuestionImportBatch::STATUS_PARSING,
        ]);

        $storedPath = $file->storeAs('question-imports', $batch->id . '_' . uniqid() . '.' . $extension, 'local');
        $fullPath = Storage::disk('local')->path($storedPath);

        try {
            [$items, $source] = $this->parse($fullPath, $extension, $parser, $ai);

            if (empty($items)) {
                $batch->update([
                    'status'        => QuestionImportBatch::STATUS_FAILED,
                    'error_message' => "No questions could be found in this file. Make sure it has clear question numbering (e.g. \"1.\"), choices (\"A.\"), and either an \"Answer:\" line or a clearly stated correct answer.",
                ]);

                return redirect()->route('faculty.test-bank.import')
                    ->with('warning', $batch->error_message);
            }

            DB::transaction(function () use ($batch, $items, $source) {
                foreach ($items as $i => $item) {
                    QuestionImportItem::create([
                        'batch_id'       => $batch->id,
                        'question_text' => $item['question_text'],
                        'question_type' => $item['question_type'],
                        'choices'       => $item['choices'],
                        'explanation'   => $item['explanation'] ?? null,
                        'difficulty'    => $item['difficulty'] ?? 'moderate',
                        'source'        => $item['confidence'] >= 70 ? 'rule' : 'ai',
                        'confidence'    => $item['confidence'],
                        'sort_order'    => $i,
                    ]);
                }
                $batch->update(['status' => QuestionImportBatch::STATUS_READY, 'parse_source' => $source]);
            });
        } catch (\Throwable $e) {
            Log::error('Question import failed.', ['batch_id' => $batch->id, 'error' => $e->getMessage()]);
            $batch->update([
                'status'        => QuestionImportBatch::STATUS_FAILED,
                'error_message' => 'Something went wrong reading this file. Please try a different file, or add these questions manually.',
            ]);

            return redirect()->route('faculty.test-bank.import')->with('warning', $batch->error_message);
        } finally {
            Storage::disk('local')->delete($storedPath);
        }

        return redirect()->route('faculty.test-bank.import.review', $batch->id);
    }

    /** Download a filled-in sample file for one format, so faculty have something to follow. */
    public function downloadTemplate(string $type, QuestionImportTemplateGenerator $generator)
    {
        if (! in_array($type, QuestionImportTemplateGenerator::TYPES, true)) {
            abort(404);
        }

        [$contents, $filename, $mime] = $generator->build($type);

        return response($contents, 200, [
            'Content-Type'        => $mime,
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Rule-based first, AI fallback second. Returns [items, 'rule'|'ai'|'mixed'].
     */
    private function parse(string $path, string $extension, QuestionImportParser $parser, AiQuestionImportService $ai): array
    {
        if (in_array($extension, QuestionImportParser::IMAGE_EXTENSIONS, true)) {
            $mime = match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png'         => 'image/png',
                'webp'        => 'image/webp',
                default       => 'image/jpeg',
            };

            return [$ai->extractFromImage($path, $mime), 'ai'];
        }

        // Spreadsheets/CSVs: try the structured-table reader first — much
        // higher confidence than the generic free-text pattern matcher.
        if (in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
            $tableItems = $parser->parseStructuredTable($path, $extension);
            if ($tableItems !== null && $this->allConfident($tableItems)) {
                return [$tableItems, 'rule'];
            }
        }

        $text = $parser->extractText($path, $extension);
        if ($text === null || trim($text) === '') {
            throw new \RuntimeException('Could not extract any text from this file.');
        }

        $ruleItems = $parser->parseFreeText($text) ?? [];
        if (! empty($ruleItems) && $this->allConfident($ruleItems)) {
            return [$ruleItems, 'rule'];
        }

        // Rule-based pass found nothing usable, or found low-confidence rows
        // (e.g. missing answers) — let the AI take a pass at the same text.
        $aiItems = $ai->extractFromText($text);

        if (empty($ruleItems)) {
            return [$aiItems, 'ai'];
        }

        // Keep whichever pass found more usable rows; if AI found nothing,
        // fall back to the rule-based rows even if some need faculty review.
        return count($aiItems) >= count($ruleItems) ? [$aiItems, 'ai'] : [$ruleItems, 'mixed'];
    }

    private function allConfident(array $items): bool
    {
        foreach ($items as $item) {
            if (($item['confidence'] ?? 0) < 70) {
                return false;
            }
        }

        return true;
    }

    /**
     * Review screen: every staged item, editable, with a topic picker per
     * item (parsing never knows the topic) and a confidence flag on rows
     * that need a closer look.
     */
    public function review(int $batch)
    {
        $importBatch = QuestionImportBatch::with(['items', 'subject'])->findOrFail($batch);

        if (! $this->canManageSubject(Auth::user(), $importBatch->subject_id)) {
            return redirect()->route('faculty.test-bank')->with('warning', self::NOT_ASSIGNED_MESSAGE);
        }

        if ($importBatch->status === QuestionImportBatch::STATUS_COMMITTED) {
            return redirect()->route('faculty.test-bank')->with('status', 'This import was already saved to the Test Bank.');
        }

        return view('faculty.test-bank-import-review', [
            'batch'  => $importBatch,
            'topics' => Topic::where('subject_id', $importBatch->subject_id)->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Commit: for every item still marked "included" in the review form,
     * validate it has a topic + a valid answer, then create a real Question.
     * Excluded/discarded rows are left in the batch (soft record) but not
     * copied. Anything invalid is reported back without losing the whole batch.
     */
    public function commit(Request $request, int $batch)
    {
        $importBatch = QuestionImportBatch::with('items')->findOrFail($batch);

        if (! $this->canManageSubject(Auth::user(), $importBatch->subject_id)) {
            return redirect()->route('faculty.test-bank')->with('warning', self::NOT_ASSIGNED_MESSAGE);
        }

        $rows = $request->input('items', []);
        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($importBatch, $rows, &$created, &$skipped) {
            foreach ($importBatch->items as $item) {
                $row = $rows[$item->id] ?? null;

                // Unchecked / not submitted = faculty excluded this row.
                if (! $row || empty($row['include'])) {
                    $item->update(['status' => QuestionImportItem::STATUS_REJECTED]);
                    continue;
                }

                $topicId = $row['topic_id'] ?? null;
                $questionText = trim($row['question_text'] ?? '');
                $type = $row['question_type'] ?? 'mcq';
                $difficulty = in_array($row['difficulty'] ?? null, ['Easy', 'Medium', 'Hard'], true) ? $row['difficulty'] : 'Medium';

                if (! $topicId || $questionText === '' || ! Topic::where('id', $topicId)->where('subject_id', $importBatch->subject_id)->exists()) {
                    $skipped++;
                    continue;
                }

                $choiceRows = $this->buildChoiceRows($type, $row);
                if ($choiceRows === null) {
                    $skipped++;
                    continue;
                }

                $question = Question::create([
                    'topic_id'      => $topicId,
                    'created_by'    => Auth::id(),
                    'question_text' => $questionText,
                    'question_type' => $type,
                    'difficulty'    => ['Easy' => 'easy', 'Medium' => 'moderate', 'Hard' => 'difficult'][$difficulty],
                    'explanation'   => trim($row['explanation'] ?? '') ?: null,
                    'is_active'     => true,
                ]);

                foreach ($choiceRows as $label => $choiceRow) {
                    $question->choices()->create(['choice_label' => $label] + $choiceRow);
                }

                $item->update(['status' => QuestionImportItem::STATUS_APPROVED, 'topic_id' => $topicId]);
                $created++;
            }

            $importBatch->update(['status' => QuestionImportBatch::STATUS_COMMITTED]);
        });

        $message = "{$created} question" . ($created === 1 ? '' : 's') . ' added to the Test Bank.';
        if ($skipped > 0) {
            $message .= " {$skipped} row" . ($skipped === 1 ? ' was' : 's were') . " skipped (missing a topic, question text, or a marked correct answer).";
        }

        return redirect()->route('faculty.test-bank')->with($skipped > 0 ? 'warning' : 'status', $message);
    }

    /** Build the label => [choice_text, is_correct] rows the form submitted, or null if invalid. */
    private function buildChoiceRows(string $type, array $row): ?array
    {
        if ($type === 'true_false') {
            $answer = $row['tf_answer'] ?? null;
            if (! in_array($answer, ['true', 'false'], true)) {
                return null;
            }

            return [
                'A' => ['choice_text' => 'True', 'is_correct' => $answer === 'true'],
                'B' => ['choice_text' => 'False', 'is_correct' => $answer === 'false'],
            ];
        }

        $rows = [];
        $correctLabel = $row['correct_label'] ?? null;
        foreach (['A', 'B', 'C', 'D'] as $label) {
            $text = trim($row['choices'][$label] ?? '');
            if ($text === '') {
                continue;
            }
            $rows[$label] = ['choice_text' => $text, 'is_correct' => $label === $correctLabel];
        }

        if (count($rows) < 2 || ! collect($rows)->contains('is_correct', true)) {
            return null;
        }

        return $rows;
    }

    /** Discard a batch (and its staged items) without saving anything. */
    public function destroy(int $batch)
    {
        $importBatch = QuestionImportBatch::findOrFail($batch);

        if (! $this->canManageSubject(Auth::user(), $importBatch->subject_id)) {
            return redirect()->route('faculty.test-bank')->with('warning', self::NOT_ASSIGNED_MESSAGE);
        }

        $importBatch->delete();

        return redirect()->route('faculty.test-bank')->with('status', 'Import discarded.');
    }

    private function subjectsFor(\App\Models\User $user)
    {
        return $user->isChair()
            ? Subject::where('is_active', true)
            : $user->assignedSubjects()->where('is_active', true);
    }

    private function canManageSubject(\App\Models\User $user, ?int $subjectId): bool
    {
        if (! $subjectId) {
            return false;
        }

        return $user->isChair() || $user->assignedSubjects()->where('subjects.id', $subjectId)->exists();
    }
}
