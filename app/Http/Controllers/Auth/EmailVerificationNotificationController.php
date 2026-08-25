<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\MailGuard;
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

        try {
            MailGuard::assertDeliverable();
            $request->user()->sendEmailVerificationNotification();
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'email' => $e->getMessage() ?: 'Could not send verification email. Check MAIL_* settings on Render.',
            ]);
        }

        return back()->with('status', 'verification-link-sent');
    }
}
