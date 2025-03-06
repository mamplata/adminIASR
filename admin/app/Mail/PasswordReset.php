<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Password;

class PasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $resetUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
        // Generate the password reset token
        $this->resetUrl = \url('reset-password/' . Password::createToken($user) . '?email=' . $user->email);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->subject('Reset Your Password')
            ->view('emails.password-reset') // <-- reference the Blade view
            ->with([
                'user' => $this->user,
                'resetUrl' => $this->resetUrl,
            ]);
    }
}
