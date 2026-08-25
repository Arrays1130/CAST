<x-app-layout>
    <div x-data="{ q: {{ \Illuminate\Support\Js::from($q) }} }" class="px-4 pb-24 pt-8 sm:px-10 lg:px-16">
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-ember">Adviser</p>
        <div class="mt-2 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="n-page-title">Students</h1>
                <p class="mt-2 text-sm text-notion-muted">Everyone registered as a student — even before they submit a paper.</p>
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
                                    @unless($student->email_verified_at)
                                        <form method="POST" action="{{ route('students.verify', $student) }}">
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
