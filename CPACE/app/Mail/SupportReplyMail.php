<?php

namespace App\Mail;

use App\Models\IssueReport;
use App\Models\IssueReportReply;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the requester when the Program Chair replies to, or resolves, their
 * Help & Support request. Guests who used "Report an issue" have no account,
 * so this email is the only way the answer reaches them.
 */
class SupportReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public IssueReport $report,
        public ?IssueReportReply $reply = null,
    ) {
    }

    public function build()
    {
        $prefix = $this->reply ? 'New reply' : 'Resolved';

        return $this->subject("CPAce Support — {$prefix}: {$this->report->title()} (#{$this->report->id})")
            ->view('emails.support-reply');
    }
}
