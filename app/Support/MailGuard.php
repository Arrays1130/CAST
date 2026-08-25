<?php

namespace App\Support;

use RuntimeException;

final class MailGuard
{
    /**
     * Ensure production can actually deliver email (not just write to logs).
     *
     * @throws RuntimeException
     */
    public static function assertDeliverable(): void
    {
        if (! app()->environment('production') && config('app.env') !== 'production') {
            return;
        }

        $mailer = (string) config('mail.default');

        if (in_array($mailer, ['log', 'array'], true)) {
            throw new RuntimeException(
                'Mail is set to “'.$mailer.'”. Set MAIL_MAILER=smtp on Render with Gmail App Password so reset/verify emails are delivered.'
            );
        }

        if ($mailer === 'smtp') {
            if (! filled(config('mail.mailers.smtp.host'))
                || ! filled(config('mail.mailers.smtp.username'))
                || ! filled(config('mail.mailers.smtp.password'))) {
                throw new RuntimeException(
                    'SMTP is incomplete. Set MAIL_HOST, MAIL_USERNAME, and MAIL_PASSWORD (Gmail App Password) on Render.'
                );
            }
        }

        if ($mailer === 'resend' && ! filled(env('RESEND_API_KEY'))) {
            throw new RuntimeException(
                'MAIL_MAILER=resend but RESEND_API_KEY is missing on Render.'
            );
        }
    }
}
