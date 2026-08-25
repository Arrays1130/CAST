<?php

namespace Tests\Feature;

use App\Notifications\VerifyEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class GoogleAppsScriptMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_email_posts_to_google_apps_script(): void
    {
        Http::fake([
            'https://script.google.com/*' => Http::response(['ok' => true], 200),
        ]);

        config([
            'mail.default' => 'google_apps_script',
            'services.google_apps_script.url' => 'https://script.google.com/macros/s/example/exec',
            'services.google_apps_script.secret' => 'cast-secret',
        ]);

        $user = User::factory()->unverified()->create([
            'email' => 'anje@example.com',
        ]);

        $user->notify(new VerifyEmail);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://script.google.com/macros/s/example/exec'
                && $request['secret'] === 'cast-secret'
                && $request['to'] === 'anje@example.com'
                && str_contains((string) $request['subject'], 'CAST');
        });
    }
}
