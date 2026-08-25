<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('papers.index', absolute: false));
        }

        // #region agent log
        $this->debugMailLog('D', 'EmailVerificationNotificationController::store:before', 'About to send verification email', [
            'mailer' => (string) config('mail.default'),
            'host' => (string) config('mail.mailers.smtp.host'),
            'port' => (int) config('mail.mailers.smtp.port'),
            'scheme' => config('mail.mailers.smtp.scheme'),
            'scheme_type' => gettype(config('mail.mailers.smtp.scheme')),
            'has_username' => filled(config('mail.mailers.smtp.username')),
            'has_password' => filled(config('mail.mailers.smtp.password')),
            'from_set' => filled(config('mail.from.address')),
            'from_matches_username' => config('mail.from.address') === config('mail.mailers.smtp.username'),
            'app_url_host' => parse_url((string) config('app.url'), PHP_URL_HOST),
        ]);
        // #endregion

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (Throwable $e) {
            report($e);

            $message = $e->getMessage();
            $class = class_basename($e);
            $hypothesis = 'B';
            if (str_contains(strtolower($message), 'scheme') || str_contains($class, 'UnsupportedScheme')) {
                $hypothesis = 'A';
            } elseif (str_contains($message, '535') || str_contains(strtolower($message), 'authenticate') || str_contains(strtolower($message), 'username and password')) {
                $hypothesis = 'B';
            } elseif (str_contains(strtolower($message), 'timed out') || str_contains(strtolower($message), 'connection') || str_contains($message, '110')) {
                $hypothesis = 'C';
            } elseif (! filled(config('mail.mailers.smtp.username')) || ! filled(config('mail.mailers.smtp.password'))) {
                $hypothesis = 'D';
            } elseif ((int) config('mail.mailers.smtp.port') === 465 || (int) config('mail.mailers.smtp.port') === 587) {
                $hypothesis = 'E';
            }

            // #region agent log
            $safeMessage = mb_substr(preg_replace('/\b[a-z0-9]{16}\b/i', '[redacted]', $message) ?? $message, 0, 180);
            $this->debugMailLog($hypothesis, 'EmailVerificationNotificationController::store:catch', 'Verification email send failed', [
                'exception_class' => $class,
                'exception_code' => (string) $e->getCode(),
                'exception_message' => $safeMessage,
                'mailer' => (string) config('mail.default'),
                'host' => (string) config('mail.mailers.smtp.host'),
                'port' => (int) config('mail.mailers.smtp.port'),
                'scheme' => config('mail.mailers.smtp.scheme'),
            ]);
            // #endregion

            return back()->withErrors([
                'email' => 'Mail error ['.$hypothesis.']: '.$class.' — '.$safeMessage,
            ]);
        }

        // #region agent log
        $this->debugMailLog('B', 'EmailVerificationNotificationController::store:success', 'Verification email sent OK', [
            'mailer' => (string) config('mail.default'),
            'port' => (int) config('mail.mailers.smtp.port'),
        ]);
        // #endregion

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function debugMailLog(string $hypothesisId, string $location, string $message, array $data = []): void
    {
        // #region agent log
        $payload = [
            'sessionId' => '09c4c4',
            'runId' => 'pre-fix',
            'hypothesisId' => $hypothesisId,
            'location' => $location,
            'message' => $message,
            'data' => $data,
            'timestamp' => (int) round(microtime(true) * 1000),
        ];

        @file_put_contents(
            base_path('debug-09c4c4.log'),
            json_encode($payload, JSON_UNESCAPED_SLASHES)."\n",
            FILE_APPEND
        );

        logger()->warning('[debug-mail] '.$message, [
            'hypothesisId' => $hypothesisId,
            'location' => $location,
            'data' => $data,
        ]);
        // #endregion
    }
}
