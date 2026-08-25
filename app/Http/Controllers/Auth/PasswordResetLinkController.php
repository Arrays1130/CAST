<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password', [
            'title' => 'Forgot password · CAST',
            'panelTitle' => 'Reset your access.',
            'panelCopy' => 'We will email a secure link so you can set a new password for your CAST account.',
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Could not send the reset email. Check that Gmail SMTP / mail settings are working on the server.',
                ]);
        }

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }
}
