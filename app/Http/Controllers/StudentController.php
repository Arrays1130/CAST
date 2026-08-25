<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isTeacher(), 403);

        $q = trim((string) $request->string('q'));

        $students = User::query()
            ->where('role', 'student')
            ->withCount([
                'papers as papers_count',
                'papers as active_papers_count' => fn ($query) => $query->whereNull('archived_at'),
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', '%'.$q.'%')
                        ->orWhere('email', 'like', '%'.$q.'%');
                });
            })
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => $students->count(),
            'verified' => $students->whereNotNull('email_verified_at')->count(),
            'pending' => $students->whereNull('email_verified_at')->count(),
            'with_papers' => $students->where('papers_count', '>', 0)->count(),
        ];

        return view('students.index', compact('students', 'stats', 'q'));
    }

    public function verify(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isTeacher(), 403);
        abort_unless($user->isStudent(), 404);

        if ($user->hasVerifiedEmail()) {
            return back()->with('status', $user->name.' is already verified.');
        }

        $user->markEmailAsVerified();

        return back()->with('status', $user->name.' can now log in without the email link.');
    }
}
