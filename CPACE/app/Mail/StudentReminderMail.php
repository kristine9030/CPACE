<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Real email sent to a student when a faculty member uses "Send Report"
 * or "Send Reminder to All" on the Student Performance page.
 */
class StudentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $studentName,
        public string $facultyName,
        public string $subjectLine,
        public string $bodyMessage,
        public ?string $ctaUrl = null,
        public ?string $ctaLabel = null,
    ) {
    }

    public function build()
    {
        return $this->subject($this->subjectLine)
            ->markdown('emails.student-reminder');
    }
}
