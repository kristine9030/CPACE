<?php

namespace App\Services;

use App\Models\MockExam;
use App\Models\MockExamAttempt;
use App\Models\Subject;
use App\Models\User;

/**
 * Certificate of Completion eligibility for the student mock-exam program.
 *
 * A student earns the certificate once they've submitted the most recently
 * published/closed mock exam in every one of the 6 CPALE subjects. "Most
 * recent" (not "any sitting ever") so a subject that gets a newer official
 * exam resets what counts, the same way the Chair/faculty think about which
 * sitting is the live one for a subject.
 *
 * The PDF this issues is a placeholder: the real certificate artwork is
 * still being designed, so download() renders a plain, clearly-labelled
 * stand-in rather than withholding the feature entirely. Swap the Blade
 * template in resources/views/student/certificate-pdf.blade.php once the
 * design is ready - nothing else here needs to change.
 */
class CertificateService
{
    /** Per-subject + overall status for one student. */
    public function status(int $studentId): array
    {
        $profile = User::find($studentId)?->studentProfile;

        $subjects = Subject::orderBy('id')->get();

        $rows = $subjects->map(function (Subject $subject) use ($studentId, $profile) {
            $exam = MockExam::where('subject_id', $subject->id)
                ->whereIn('status', [MockExam::STATUS_PUBLISHED, MockExam::STATUS_CLOSED])
                ->get()
                ->filter(fn (MockExam $e) => $e->admitsStudent($profile?->year_level, $profile?->section, $profile?->batch_year))
                ->sortByDesc('scheduled_at')
                ->first();

            $attempt = $exam
                ? MockExamAttempt::where('exam_id', $exam->id)->where('student_id', $studentId)->first()
                : null;

            $done = $attempt?->isSubmitted() ?? false;

            return [
                'subject_id' => $subject->id,
                'name' => $subject->name,
                'code' => $subject->code,
                'exam_available' => $exam !== null,
                'done' => $done,
                'submitted_at' => $done ? $attempt->submitted_at : null,
            ];
        });

        $completed = $rows->filter(fn ($r) => $r['done']);
        $eligible = $rows->isNotEmpty() && $completed->count() === $rows->count();

        return [
            'subjects' => $rows->values(),
            'completed' => $completed->count(),
            'total' => $rows->count(),
            'eligible' => $eligible,
            'earned_at' => $eligible ? $completed->max(fn ($r) => $r['submitted_at']) : null,
        ];
    }

    public function isEligible(int $studentId): bool
    {
        return $this->status($studentId)['eligible'];
    }
}
