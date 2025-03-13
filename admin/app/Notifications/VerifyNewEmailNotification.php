<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyNewEmailNotification extends Notification
{
    protected $verificationUrl;

    public function __construct($verificationUrl)
    {
        $this->verificationUrl = $verificationUrl;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Verify Your New Email Address')
            ->line('Please click the button below to verify your new email address.')
            ->action('Verify Email', $this->verificationUrl)
            ->line('If you did not request this change, please ignore this email.');
    }
}
