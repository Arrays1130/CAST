<?php

namespace App\Support;

use RuntimeException;
use Throwable;

final class MailFailures
{
    public static function gmailSmtpBlockedMessage(): string
    {
        return 'Render cannot reach smtp.gmail.com (port 587 times out). Use the Google Apps Script mail relay (GOOGLE_APPS_SCRIPT_URL) or ask an adviser to open Students → Confirm email.';
    }

    public static function friendly(Throwable $e): string
    {
        $raw = $e->getMessage();

        if ($raw !== '' && str_contains($raw, 'Students → Confirm email')) {
            return $raw;
        }

        $haystack = strtolower($raw);

        if (str_contains($haystack, 'smtp.gmail.com')
            || str_contains($haystack, 'connection timed out')
            || str_contains($haystack, 'stream_socket_client')) {
            return self::gmailSmtpBlockedMessage();
        }

        return $raw !== '' ? $raw : 'Could not send email. Check mail settings on Render.';
    }
}
