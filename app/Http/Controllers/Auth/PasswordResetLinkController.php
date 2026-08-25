<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\MailFailures;
use App\Support\MailGuard;
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
    public function create(Request $request): View
    {
        $portal = $this->portalFromRequest($request);

        return view('auth.forgot-password', [
            'title' => 'Forgot password · CAST',
            'panelTitle' => 'Reset your access.',
            'panelCopy' => 'We will email a secure link so you can set a new password for your CAST account.',
            'portal' => $portal,
            'loginRoute' => $portal === 'adviser' ? 'login.adviser' : ($portal === 'student' ? 'login.student' : 'login'),
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
            'portal' => ['nullable', 'in:student,adviser'],
        ]);

        $portal = $this->portalFromRequest($request);
        if ($portal) {
            $request->session()->put('password_reset_portal', $portal);
        }

        try {
            MailGuard::assertDeliverable();

            $status = Password::sendResetLink(
                $request->only('email')
            );

            $account = User::query()->where('email', $request->string('email')->toString())->first();
            if ($account) {
                $request->session()->put(
                    'password_reset_portal',
                    $account->isTeacher() ? 'adviser' : 'student'
                );
            }
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput($request->only('email', 'portal'))
                ->withErrors([
                    'email' => MailFailures::friendly($e),
                ]);
        }

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email', 'portal'))
                ->withErrors(['email' => __($status)]);
    }

    private function portalFromRequest(Request $request): ?string
    {
        $portal = $request->query('portal', $request->input('portal'));

        return in_array($portal, ['student', 'adviser'], true) ? $portal : null;
    }
}
