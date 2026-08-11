<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Real email sent to a student or faculty member when the Program Chair
 * provisions their account (or reissues a one-time password). The OTP
 * only ever reaches the account owner's own inbox — the Program Chair
 * never sees it.
 */
class AccountCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $tempPassword,
        public string $roleLabel,
        public bool $isReissue = false,
    ) {
    }

    public function build()
    {
        return $this->subject($this->isReissue ? 'Your CPACE one-time password was reset' : 'Your CPACE account is ready')
            ->view('emails.account-credentials');
    }
}
