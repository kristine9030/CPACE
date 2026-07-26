<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Real email sent when a user requests a password reset from the
 * "Forgot your password?" screen.
 */
class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $resetUrl,
    ) {
    }

    public function build()
    {
        return $this->subject('Reset your CPACE password')
            ->view('emails.reset-password');
    }
}
