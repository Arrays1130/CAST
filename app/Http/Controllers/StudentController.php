<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
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

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isTeacher(), 403);

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'roster' => ['nullable', 'string', 'max:8000'],
        ]);

        $password = $validated['password'];
        $created = [];
        $skipped = [];

        $roster = trim((string) ($validated['roster'] ?? ''));
        if ($roster !== '') {
            foreach (preg_split('/\R+/', $roster) ?: [] as $line) {
                $parsed = $this->parseRosterLine($line);
                if ($parsed === null) {
                    continue;
                }

                if (User::query()->where('email', $parsed['email'])->exists()) {
                    $skipped[] = $parsed['email'];
                    continue;
                }

                $created[] = $this->provisionStudent($parsed['name'], $parsed['email'], $password);
            }
        } else {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            ]);

            $created[] = $this->provisionStudent($validated['name'], $validated['email'], $password);
        }

        if ($created === [] && $skipped === []) {
            return back()->withErrors([
                'roster' => 'Add a name and email, or paste a list (one student per line).',
            ])->withInput();
        }

        $message = count($created) === 1
            ? $created[0]['name'].' can log in now. Give them the temporary password, then they must change it.'
            : count($created).' students can log in now with the temporary password. They must change it on first login.';

        if ($skipped !== []) {
            $message .= ' Skipped existing: '.implode(', ', $skipped).'.';
        }

        return back()->with([
            'status' => $message,
            'provisioned' => [
                'password' => $password,
                'students' => $created,
            ],
        ]);
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isTeacher(), 403);
        abort_unless($user->isStudent(), 404);

        $validated = $request->validate([
            'password' => ['required', Password::defaults()],
        ]);

        $user->update([
            'password' => $validated['password'],
            'must_change_password' => true,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        return back()->with([
            'status' => 'Temporary password set for '.$user->name.'. They must change it at next login.',
            'provisioned' => [
                'password' => $validated['password'],
                'students' => [[
                    'name' => $user->name,
                    'email' => $user->email,
                ]],
            ],
        ]);
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

    /**
     * @return array{name: string, email: string}|null
     */
    private function parseRosterLine(string $line): ?array
    {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            return null;
        }

        $parts = preg_split('/\s*[|,]\s*/', $line, 2) ?: [];
        if (count($parts) < 2) {
            return null;
        }

        $name = trim($parts[0]);
        $email = strtolower(trim($parts[1]));

        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return ['name' => $name, 'email' => $email];
    }

    /**
     * @return array{name: string, email: string}
     */
    private function provisionStudent(string $name, string $email, string $password): array
    {
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'student',
            'email_verified_at' => now(),
            'must_change_password' => true,
        ]);

        return ['name' => $name, 'email' => $email];
    }
}
