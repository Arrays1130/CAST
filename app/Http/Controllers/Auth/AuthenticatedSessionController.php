<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the role chooser.
     */
    public function create(): View
    {
        return view('auth.login', [
            'title' => 'Log in · CAST',
            'panelTitle' => 'Choose your studio entrance.',
            'panelCopy' => 'Student and adviser accounts are separate. Pick the portal that matches your role.',
        ]);
    }

    /**
     * Display the student login form.
     */
    public function createStudent(): View
    {
        return view('auth.login-form', [
            'portal' => 'student',
            'role' => 'student',
            'title' => 'Student log in · CAST',
            'panelTitle' => 'Student workspace.',
            'panelCopy' => 'Upload manuscripts, track feedback, and prepare for defense.',
            'heading' => 'Student log in',
            'subheading' => 'For capstone authors only. Advisers should use the Adviser portal.',
            'cta' => 'Enter student studio',
            'switchLabel' => 'Are you an adviser?',
            'switchRoute' => 'login.adviser',
            'switchText' => 'Go to Adviser login',
            'showRegister' => true,
        ]);
    }

    /**
     * Display the adviser login form.
     */
    public function createAdviser(): View
    {
        return view('auth.login-form', [
            'portal' => 'adviser',
            'role' => 'teacher',
            'title' => 'Adviser log in · CAST',
            'panelTitle' => 'Adviser review desk.',
            'panelCopy' => 'Open papers live, leave feedback, score work, and clear students for defense.',
            'heading' => 'Adviser log in',
            'subheading' => 'For teachers and advisers only. Students must use the Student portal.',
            'cta' => 'Enter adviser desk',
            'switchLabel' => 'Are you a student?',
            'switchRoute' => 'login.student',
            'switchText' => 'Go to Student login',
            'showRegister' => false,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if ($request->user()?->must_change_password) {
            return redirect()->route('password.change');
        }

        return redirect()->intended(route('papers.index', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
