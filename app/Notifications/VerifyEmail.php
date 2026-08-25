<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends BaseVerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify your CAST Studio email')
            ->greeting('Welcome to CAST Studio')
            ->line('Confirm this email so you can open your capstone workspace.')
            ->action('Verify email address', $verificationUrl)
            ->line('If you did not create a CAST account, you can ignore this message.')
            ->salutation('— CAST Studio');
    }
}
