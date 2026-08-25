<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset your CAST Studio password')
            ->greeting('Password reset')
            ->line('We received a request to reset the password for your CAST Studio account.')
            ->action('Reset password', $this->resetUrl($notifiable))
            ->line('This link expires in '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire').' minutes.')
            ->line('If you did not request a reset, you can ignore this email.')
            ->salutation('— CAST Studio');
    }
}
