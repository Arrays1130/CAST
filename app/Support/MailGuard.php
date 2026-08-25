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
                'Mail is set to “'.$mailer.'”. On Render use MAIL_MAILER=resend with RESEND_API_KEY (Gmail SMTP is blocked).'
            );
        }

        if ($mailer === 'smtp') {
            $host = strtolower((string) config('mail.mailers.smtp.host'));

            if (config('cast.on_render') && str_contains($host, 'gmail.com')) {
                throw new RuntimeException(MailFailures::gmailSmtpBlockedMessage());
            }

            if (! filled(config('mail.mailers.smtp.host'))
                || ! filled(config('mail.mailers.smtp.username'))
                || ! filled(config('mail.mailers.smtp.password'))) {
                throw new RuntimeException(
                    'SMTP is incomplete. On Render prefer Resend (RESEND_API_KEY + MAIL_MAILER=resend) instead of Gmail SMTP.'
                );
            }
        }

        if ($mailer === 'resend' && ! filled(config('services.resend.key'))) {
            throw new RuntimeException(
                'MAIL_MAILER=resend but RESEND_API_KEY is missing on Render.'
            );
        }
    }
}
