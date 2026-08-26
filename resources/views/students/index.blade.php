<x-app-layout>
    <div x-data="{ q: {{ \Illuminate\Support\Js::from($q) }} }" class="px-4 pb-24 pt-8 sm:px-10 lg:px-16">
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-ember">Adviser</p>
        <div class="mt-2 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="n-page-title">Students</h1>
                <p class="mt-2 text-sm text-notion-muted">Create logins with emails you already have. Students only change their password on first login.</p>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="stat-chip">
                <div class="text-xs text-notion-faint">Registered</div>
                <div class="mt-1 font-display text-2xl">{{ $stats['total'] }}</div>
            </div>
            <div class="stat-chip">
                <div class="text-xs text-notion-faint">Verified</div>
                <div class="mt-1 font-display text-2xl">{{ $stats['verified'] }}</div>
            </div>
            <div class="stat-chip">
                <div class="text-xs text-notion-faint">Pending email</div>
                <div class="mt-1 font-display text-2xl">{{ $stats['pending'] }}</div>
            </div>
            <div class="stat-chip">
                <div class="text-xs text-notion-faint">With papers</div>
                <div class="mt-1 font-display text-2xl">{{ $stats['with_papers'] }}</div>
            </div>
        </div>

        <form method="GET" action="{{ route('students.index') }}" class="mt-6">
            <input
                type="search"
                name="q"
                value="{{ $q }}"
                placeholder="Search name or email…"
                class="field max-w-md"
            >
        </form>

        @if(session('provisioned'))
            <div class="surface mt-6 border border-ember/20 p-5">
                <h2 class="font-display text-xl">Give these to your students</h2>
                <p class="mt-1 text-sm text-notion-muted">Login: <span class="font-medium text-ink">Student portal</span>. Temporary password (same for this batch):</p>
                <p class="mt-3 font-mono text-lg">{{ session('provisioned.password') }}</p>
                <ul class="mt-4 space-y-1 text-sm">
                    @foreach(session('provisioned.students') as $row)
                        <li><span class="font-medium">{{ $row['name'] }}</span> · {{ $row['email'] }}</li>
                    @endforeach
                </ul>
                <p class="mt-3 text-xs text-notion-muted">They will be asked to change this password on first login. Copy it now — it is not shown again.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('students.store') }}" class="surface mt-6 space-y-4 p-5">
            @csrf
            <h2 class="font-display text-xl">Create student accounts</h2>
            <p class="text-sm text-notion-muted">You already have their emails. Create the CAST login here — no verification email needed. They log in, then change the password.</p>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" class="block w-full" type="text" name="name" :value="old('name')" autocomplete="off" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" autocomplete="off" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
            </div>

            <div>
                <x-input-label for="roster" value="Or paste a list (one per line: Name, email)" />
                <textarea id="roster" name="roster" rows="4" class="field" placeholder="Ana Cruz, ana@gmail.com&#10;Ben Santos, ben@gmail.com">{{ old('roster') }}</textarea>
                <x-input-error :messages="$errors->get('roster')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="password" value="Temporary password" />
                <x-text-input id="password" class="block w-full max-w-md" type="text" name="password" required autocomplete="off" placeholder="e.g. Cast2026!" />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
                <p class="mt-1 text-xs text-notion-muted">Tell the class this password. They must change it after login.</p>
            </div>

            <x-primary-button>Create account</x-primary-button>
        </form>

        <div class="surface mt-6 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-notion-line bg-paper/80 text-xs uppercase tracking-wide text-notion-faint">
                        <tr>
                            <th class="px-4 py-3 font-medium">Student</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Papers</th>
                            <th class="px-4 py-3 font-medium">Joined</th>
                            <th class="px-4 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-notion-line">
                        @forelse($students as $student)
                            <tr class="hover:bg-white/70">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="grid h-7 w-7 place-items-center rounded-full bg-[#7c3aed] text-[10px] font-semibold text-white">{{ $student->initials() }}</span>
                                        <span class="font-medium">{{ $student->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-notion-muted">{{ $student->email }}</td>
                                <td class="px-4 py-3">
                                    @if($student->email_verified_at)
                                        <span class="rounded-full bg-[#d8f0d8] px-2 py-0.5 text-xs text-[#1b3d24]">Verified</span>
                                    @else
                                        <span class="rounded-full bg-[#ffe1cc] px-2 py-0.5 text-xs text-[#6a3212]">Pending email</span>
                                    @endif
                                    @if($student->must_change_password)
                                        <span class="ml-1 rounded-full bg-paper px-2 py-0.5 text-xs text-notion-muted">Must change password</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    {{ $student->active_papers_count }}
                                    <span class="text-notion-faint">active</span>
                                    @if($student->papers_count !== $student->active_papers_count)
                                        <span class="text-notion-faint">· {{ $student->papers_count }} total</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-notion-muted">{{ $student->created_at?->format('M j, Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <details class="text-left">
                                        <summary class="cursor-pointer text-xs font-medium text-ember">Reset password</summary>
                                        <form method="POST" action="{{ route('students.password', $student) }}" class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center">
                                            @csrf
                                            <input type="text" name="password" required placeholder="New temp password" class="field !py-1.5 text-xs" autocomplete="off">
                                            <button type="submit" class="rounded-lg border border-notion-line bg-white px-3 py-1.5 text-xs font-medium hover:bg-paper">Set</button>
                                        </form>
                                    </details>
                                    @unless($student->email_verified_at)
                                        <form method="POST" action="{{ route('students.verify', $student) }}" class="mt-2">
                                            @csrf
                                            <button type="submit" class="rounded-lg border border-notion-line bg-white px-3 py-1.5 text-xs font-medium hover:bg-paper">Confirm email</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-notion-muted">
                                    {{ $q !== '' ? 'No students match that search.' : 'No students registered yet.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
