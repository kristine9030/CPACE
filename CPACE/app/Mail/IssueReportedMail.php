<?php

namespace App\Mail;

use App\Models\IssueReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the support inbox when someone submits "Report an issue".
 *
 * Replies go to the reporter rather than to the support address itself, so
 * answering the alert answers the person.
 */
class IssueReportedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public IssueReport $report)
    {
    }

    public function build()
    {
        return $this->subject('CPAce — ' . $this->report->categoryLabel() . ' (#' . $this->report->id . ')')
            ->replyTo($this->report->email, $this->report->name)
            ->view('emails.issue-reported');
    }
}
