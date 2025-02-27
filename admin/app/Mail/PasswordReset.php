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

    public function __construct($user)
    {
        $this->user = $user;
        // Generate the password reset token
        $this->resetUrl = url('reset-password/' . Password::createToken($user) . '?email=' . $user->email);
    }


    public function build()
    {
        return $this->subject('Reset Your Password')
            ->html(
                "<p>Hello {$this->user->name},</p>
                         <p>Your account has been created. Please click the link below to set a new password:</p>
                         <a href='{$this->resetUrl}'>Reset Password</a>"
            );
    }
}
