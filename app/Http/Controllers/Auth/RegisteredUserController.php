<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\MailFailures;
use App\Support\MailGuard;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'invite_code' => ['nullable', 'string', 'max:100'],
        ]);

        $invite = (string) config('cast.teacher_invite_code');
        $isTeacher = $invite !== ''
            && hash_equals($invite, (string) $request->input('invite_code'));

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $isTeacher ? 'teacher' : 'student',
        ]);

        try {
            MailGuard::assertDeliverable();
            event(new Registered($user));
        } catch (Throwable $e) {
            report($e);
            Auth::login($user);

            return redirect()
                ->route('verification.notice')
                ->withErrors([
                    'email' => MailFailures::friendly($e),
                ]);
        }

        Auth::login($user);

        return redirect()->intended(route('verification.notice', absolute: false));
    }
}
