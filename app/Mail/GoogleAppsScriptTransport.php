<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class GoogleAppsScriptTransport extends AbstractTransport
{
    public function __construct(
        private readonly string $url,
        private readonly string $secret,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $to = collect($email->getTo())
            ->map(fn ($address) => $address->getAddress())
            ->filter()
            ->implode(',');

        if ($to === '' || $this->url === '' || $this->secret === '') {
            throw new RuntimeException('Google Apps Script mail is not configured (URL/secret/recipient).');
        }

        $html = (string) ($email->getHtmlBody() ?: '');
        $text = (string) ($email->getTextBody() ?: '');

        if ($html === '' && $text !== '') {
            $html = nl2br(e($text));
        }

        $response = Http::timeout(20)
            ->acceptJson()
            ->asJson()
            ->post($this->url, [
                'secret' => $this->secret,
                'to' => $to,
                'subject' => $email->getSubject() ?: 'CAST Studio',
                'html' => $html,
                'text' => $text !== '' ? $text : strip_tags($html),
                'from_name' => (string) config('mail.from.name', 'CAST Studio'),
            ]);

        if ($response->failed() || $response->json('ok') === false) {
            throw new RuntimeException(
                $response->json('error') ?: 'Google Apps Script could not send mail (HTTP '.$response->status().').'
            );
        }
    }

    public function __toString(): string
    {
        return 'google_apps_script';
    }
}
