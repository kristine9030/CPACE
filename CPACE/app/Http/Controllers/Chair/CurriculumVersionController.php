<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Controller;
use App\Models\CurriculumAudit;
use App\Models\CurriculumVersion;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\CurriculumQuestionCopier;
use App\Support\BatchYear;
use App\Support\CurriculumAuditor;
use App\Support\CurriculumScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Lifecycle of a curriculum version: start a blank DRAFT, publish it (the old
 * active one becomes read-only history), or discard it. While a draft exists
 * students keep studying the published curriculum; nothing they see changes
 * until the chair publishes.
 */
class CurriculumVersionController extends Controller
{
    /** Start a new, empty draft curriculum. */
    public function store(Request $request)
    {
        if (CurriculumScope::draftId() !== null) {
            return back()->with('error', 'A draft curriculum is already in progress. Publish or discard it before starting another.');
        }

        $data = $this->validateDetails($request);

        $version = CurriculumVersion::create([
            'label' => $data['label'],
            'effective_from_batch' => $data['effective_from_batch'],
            'status' => CurriculumVersion::STATUS_DRAFT,
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);
        CurriculumScope::flush();

        CurriculumAuditor::record($version, Auth::user(), CurriculumAudit::ACTION_CREATED,
            "Effective from batch {$version->effective_from_batch}");

        $this->notifyFaculty(
            'New curriculum in preparation',
            "The Program Chair started a new curriculum, \"{$version->label}\" (from batch {$version->effective_from_batch}). "
                . 'Once its topics are in, open the Test Bank, switch to the draft curriculum and prepare the questions for your subjects.',
            $version
        );

        return redirect()->route('chair.subjects', ['version' => $version->id])
            ->with('status', 'Draft curriculum started. Students keep using the current curriculum until you publish this one.');
    }

    /**
     * Rename a draft or move its starting batch. Only while it's a draft:
     * once published, the previous curriculum's end batch has been set from
     * this start batch, so changing it then would leave a gap or overlap.
     */
    public function update(Request $request, CurriculumVersion $version)
    {
        if (! $version->isDraft()) {
            return back()->with('error', 'Only a draft curriculum\'s name and first batch can be changed. A published curriculum\'s batch range is locked, since students are already assigned to it.');
        }

        $data = $this->validateDetails($request);
        $before = $version->only(['label', 'effective_from_batch']);

        $version->update($data);
        CurriculumScope::flush();

        $changes = collect(['label' => 'Name', 'effective_from_batch' => 'First batch'])
            ->filter(fn ($name, $field) => (string) $before[$field] !== (string) $version->{$field})
            ->map(fn ($name, $field) => "{$name}: {$before[$field]} → {$version->{$field}}")
            ->implode('; ');
        if ($changes !== '') {
            CurriculumAuditor::record($version, Auth::user(), CurriculumAudit::ACTION_EDITED, $changes);
        }

        return redirect()->route('chair.subjects', ['version' => $version->id])
            ->with('status', 'Curriculum details updated.');
    }

    /**
     * A curriculum's name and first batch. The first batch must come AFTER
     * the published curriculum's first batch: a new curriculum takes over
     * from a later batch onward, and publishing closes the current one at
     * the batch just before it. Starting it at or before the current one
     * would give the two overlapping or backwards ranges.
     *
     * @return array{label: string, effective_from_batch: string}
     */
    private function validateDetails(Request $request): array
    {
        $active = CurriculumVersion::where('status', CurriculumVersion::STATUS_ACTIVE)->first();

        return $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'effective_from_batch' => ['required', 'string', function ($attribute, $value, $fail) use ($active) {
                if (! BatchYear::isValid($value)) {
                    $fail('Enter the first batch as a school year, e.g. ' . BatchYear::current() . '.');

                    return;
                }
                if ($active?->effective_from_batch && $value <= $active->effective_from_batch) {
                    $fail("The first batch must come after the current curriculum's first batch ({$active->effective_from_batch}), "
                        . 'e.g. ' . BatchYear::next($active->effective_from_batch) . ' or later. '
                        . "Batches up to the one before it keep \"{$active->label}\".");
                }
            }],
        ]);
    }

    /** Publish the draft: it becomes the curriculum students study. */
    public function publish(CurriculumVersion $version)
    {
        abort_unless($version->isDraft(), 422, 'Only a draft curriculum can be published.');

        $empty = Subject::where('is_active', true)
            ->whereDoesntHave('topics', fn ($q) => $q->where('curriculum_version_id', $version->id)->where('is_active', true))
            ->orderBy('id')
            ->pluck('code');

        if ($empty->isNotEmpty()) {
            return back()->with('error', 'This draft has no active topics yet for: ' . $empty->implode(', ')
                . '. Students would have nothing to practice in those subjects, so add or import their topics before publishing.');
        }

        DB::transaction(function () use ($version) {
            $previous = CurriculumVersion::where('status', CurriculumVersion::STATUS_ACTIVE)->lockForUpdate()->get();
            foreach ($previous as $old) {
                $old->update([
                    'status' => CurriculumVersion::STATUS_ARCHIVED,
                    'effective_to_batch' => $old->effective_to_batch
                        ?? ($version->effective_from_batch ? BatchYear::previous($version->effective_from_batch) : null),
                ]);
            }

            $version->update(['status' => CurriculumVersion::STATUS_ACTIVE, 'published_at' => now()]);
        });
        CurriculumScope::flush();
        session()->forget(CurriculumScope::TEST_BANK_SESSION_KEY);

        CurriculumAuditor::record($version, Auth::user(), CurriculumAudit::ACTION_PUBLISHED);

        $this->notifyFaculty(
            'New curriculum is now live',
            "\"{$version->label}\" is now the curriculum students study. Quizzes and the Test Bank now use its topics; "
                . 'review your subjects\' questions against it.',
            $version
        );

        return redirect()->route('chair.subjects')
            ->with('status', "\"{$version->label}\" is now the active curriculum. The previous one is kept as read-only history.");
    }

    /** Throw away a draft and everything prepared under it. */
    public function destroy(CurriculumVersion $version)
    {
        abort_unless($version->isDraft(), 422, 'Only a draft curriculum can be discarded.');

        $topicIds = Topic::where('curriculum_version_id', $version->id)->pluck('id');

        DB::transaction(function () use ($version, $topicIds) {
            // Draft questions are never shown to students, so nothing else
            // references them; choices and variants cascade.
            Question::whereIn('topic_id', $topicIds)->delete();
            Topic::whereIn('id', $topicIds)->update(['parent_id' => null]);
            Topic::whereIn('id', $topicIds)->delete();
            $version->audits()->delete();
            $version->delete();
        });
        CurriculumScope::flush();
        session()->forget(CurriculumScope::TEST_BANK_SESSION_KEY);

        return redirect()->route('chair.subjects')->with('status', 'The draft curriculum was discarded. Nothing students see has changed.');
    }

    /** Copy one subject's Test Bank from the active curriculum into the draft. */
    public function copyQuestions(CurriculumVersion $version, Subject $subject, CurriculumQuestionCopier $copier)
    {
        abort_unless($version->isDraft(), 422, 'Questions can only be copied into a draft curriculum.');

        $activeId = CurriculumScope::activeId();
        if ($activeId === null) {
            return back()->with('error', 'There is no published curriculum to copy questions from.');
        }

        if (! Topic::where('curriculum_version_id', $version->id)->where('subject_id', $subject->id)->exists()) {
            return back()->with('error', "Add or import {$subject->code}'s topics first, so the questions have somewhere to go.");
        }

        $result = $copier->copy($subject->id, $activeId, $version->id);

        $details = "{$result['questions']} question(s) into {$result['topics']} topic(s)"
            . ($result['unmatched'] > 0 ? "; {$result['unmatched']} old topic(s) had no matching topic" : '');
        CurriculumAuditor::record($version, Auth::user(), CurriculumAudit::ACTION_QUESTIONS_COPIED, $details, $subject->id);

        $message = $result['questions'] > 0
            ? "Copied {$result['questions']} {$subject->code} question(s) into {$result['topics']} matching topic(s). Faculty can now review them and delete what no longer applies."
            : "No new {$subject->code} questions were copied — none of the current topics with questions match a topic in this draft (or they were already copied).";
        if ($result['unmatched'] > 0) {
            $message .= " {$result['unmatched']} current topic(s) with questions had no match by name.";
        }

        return back()->with('status', $message);
    }

    /**
     * One notification per assigned faculty member (not one per subject), so
     * a faculty teaching three subjects isn't told the same thing three times.
     */
    private function notifyFaculty(string $title, string $message, CurriculumVersion $version): void
    {
        $facultyIds = DB::table('faculty_subjects')->distinct()->pluck('faculty_id');
        if ($facultyIds->isEmpty()) {
            return;
        }

        DB::table('notifications')->insert($facultyIds->map(fn ($id) => [
            'recipient_id' => $id,
            'type' => 'curriculum_update',
            'title' => $title,
            'message' => $message,
            'link' => route('faculty.test-bank'),
            'is_read' => 0,
            'reference_type' => 'curriculum_version',
            'reference_id' => $version->id,
            'created_at' => now(),
        ])->all());
    }
}
