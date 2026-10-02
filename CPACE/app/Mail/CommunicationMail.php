<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Real email sent to each recipient of a chair Student/Faculty announcement,
 * mirroring the in-app notification created in CommunicationController.
 */
class CommunicationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $senderName,
        public string $title,
        public string $body,
        public string $priority,
        public ?string $ctaUrl = null,
        /** File names attached to the announcement (opened inside CPACE, not attached to the email). */
        public array $attachmentNames = [],
    ) {
    }

    public function build()
    {
        return $this->subject($this->title)
            ->view('emails.communication');
    }
}
