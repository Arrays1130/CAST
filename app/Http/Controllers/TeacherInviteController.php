<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeacherInviteController extends Controller
{
    public function promote(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isTeacher(), 403);

        $validated = $request->validate([
            'email' => ['required', 'email', Rule::exists('users', 'email')],
        ]);

        $user = User::query()->where('email', $validated['email'])->firstOrFail();

        if ($user->isTeacher()) {
            return back()->with('status', $user->name.' is already an adviser.');
        }

        $user->update(['role' => 'teacher']);

        return back()->with('status', $user->name.' is now an adviser.');
    }
}
