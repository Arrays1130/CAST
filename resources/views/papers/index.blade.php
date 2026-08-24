<x-app-layout>
    @php
        $columns = [
            'submitted' => ['label' => 'Submitted', 'color' => 'bg-[#ece7de] text-[#3b372f]', 'items' => $papers->where('status', \App\Enums\PaperStatus::Submitted)],
            'for_review' => ['label' => 'For review', 'color' => 'bg-[#d7ecf6] text-[#16384a]', 'items' => $papers->where('status', \App\Enums\PaperStatus::ForReview)],
            'needs_revision' => ['label' => 'Needs revision', 'color' => 'bg-[#ffe1cc] text-[#6a3212]', 'items' => $papers->where('status', \App\Enums\PaperStatus::NeedsRevision)],
            'approved' => ['label' => 'Approved', 'color' => 'bg-[#d8f0d8] text-[#1b3d24]', 'items' => $papers->where('status', \App\Enums\PaperStatus::Approved)],
        ];
        $colspan = auth()->user()->isTeacher() ? 8 : 7;
    @endphp

    <div x-data="{ q: '', view: $persist('table') }" class="px-4 pb-24 pt-8 sm:px-10 lg:px-16">
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-ember">Workspace</p>
        <div class="mt-2 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="n-page-title">{{ $archived ? 'Archive' : 'Papers' }}</h1>
                <p class="mt-2 text-sm text-notion-muted">{{ $archived ? 'Pages you tucked away.' : 'Capstone manuscripts in production.' }}</p>
            </div>
            @if(auth()->user()->isStudent())
                <a href="{{ route('papers.create') }}" class="btn-primary">New paper</a>
            @endif
        </div>

        <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            <div class="stat-chip">
                <div class="text-xs text-notion-faint">Pages</div>
                <div class="mt-1 font-display text-2xl">{{ $stats['total'] }}</div>
            </div>
            <div class="stat-chip">
                <div class="text-xs text-notion-faint">In review</div>
                <div class="mt-1 font-display text-2xl">{{ $stats['for_review'] }}</div>
            </div>
            <div class="stat-chip">
                <div class="text-xs text-notion-faint">Revisions</div>
                <div class="mt-1 font-display text-2xl">{{ $stats['needs_revision'] }}</div>
            </div>
            <div class="stat-chip">
                <div class="text-xs text-notion-faint">Approved</div>
                <div class="mt-1 font-display text-2xl">{{ $stats['approved'] }}</div>
            </div>
            <div class="stat-chip col-span-2 sm:col-span-1">
                <div class="text-xs text-notion-faint">Overdue</div>
                <div class="mt-1 font-display text-2xl {{ ($stats['overdue'] ?? 0) ? 'text-ember' : '' }}">{{ $stats['overdue'] ?? 0 }}</div>
            </div>
        </div>

        <div class="mt-8 flex flex-wrap items-center gap-1 rounded-2xl border border-notion-line bg-white/60 p-1 text-sm shadow-card">
            <button type="button" @click="view = 'table'" class="min-h-11 rounded-xl px-3 py-1.5" :class="view === 'table' ? 'bg-ink text-white' : 'text-notion-muted hover:bg-white'">List</button>
            <button type="button" @click="view = 'board'" class="min-h-11 rounded-xl px-3 py-1.5" :class="view === 'board' ? 'bg-ink text-white' : 'text-notion-muted hover:bg-white'">Board</button>
            <a href="{{ route('papers.index', $archived ? [] : ['archived' => 1]) }}" class="inline-flex min-h-11 items-center rounded-xl px-3 py-1.5 text-notion-muted hover:bg-white">{{ $archived ? 'Active' : 'Archive' }}</a>
            @if(auth()->user()->isTeacher())
                <a href="{{ route('papers.export', $archived ? ['archived' => 1] : []) }}" class="hidden min-h-11 items-center rounded-xl px-3 py-1.5 text-notion-muted hover:bg-white sm:inline-flex">Export CSV</a>
            @endif
            <div class="w-full p-1 sm:ml-auto sm:w-auto">
                <input x-model="q" type="search" placeholder="Filter…" class="w-full rounded-xl border-0 bg-transparent py-2.5 text-base placeholder:text-notion-faint focus:ring-0 sm:w-44 sm:py-1.5 sm:text-sm">
            </div>
        </div>

        <div x-show="view === 'table'" class="mt-4">
            <div class="space-y-3 md:hidden">
                @forelse($papers as $paper)
                    @php($haystack = strtolower($paper->title.' '.$paper->group_name.' '.$paper->student->name.' '.$paper->tags))
                    <a href="{{ route('papers.show', $paper) }}" x-show="{{ json_encode($haystack) }}.includes(q.toLowerCase())" class="surface block p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="truncate font-medium">{{ $paper->title }}</div>
                                <div class="mt-1 text-sm text-notion-muted">{{ $paper->group_name ?: $paper->student->name }}</div>
                            </div>
                            <x-status-badge :status="$paper->status" />
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-notion-muted">
                            @if($paper->isOverdue())
                                <span class="font-medium text-ember">Due {{ $paper->due_at?->format('M j') }}</span>
                            @else
                                <span>Due {{ $paper->due_at?->format('M j') ?: '—' }}</span>
                            @endif
                            <span>Score {{ $paper->score !== null ? $paper->score : '—' }}</span>
                            @if($paper->drive())
                                <span class="rounded-full bg-[#e8f0fe] px-2 py-0.5 font-semibold text-[#1967d2]">Drive</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="surface px-4 py-14 text-center">
                        <div class="font-display text-2xl">Empty stage</div>
                        <p class="mt-2 text-sm text-notion-faint">{{ $archived ? 'Nothing archived yet.' : 'Drop the first manuscript into the studio.' }}</p>
                        @if(auth()->user()->isStudent() && ! $archived)
                            <a href="{{ route('papers.create') }}" class="btn-primary mt-5">New paper</a>
                        @endif
                    </div>
                @endforelse
            </div>

            <div class="hidden overflow-hidden rounded-3xl border border-notion-line bg-white/70 shadow-card md:block">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[780px] border-collapse text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-notion-faint">
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-3 py-3 font-medium">Group</th>
                            @if(auth()->user()->isTeacher())
                                <th class="px-3 py-3 font-medium">Student</th>
                            @endif
                            <th class="px-3 py-3 font-medium">Status</th>
                            <th class="px-3 py-3 font-medium">Tags</th>
                            <th class="px-3 py-3 font-medium">Due</th>
                            <th class="px-3 py-3 font-medium">Score</th>
                            <th class="px-4 py-3 font-medium">Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($papers as $paper)
                            <tr
                                @php($haystack = strtolower($paper->title.' '.$paper->group_name.' '.$paper->student->name.' '.$paper->tags))
                                x-show="{{ json_encode($haystack) }}.includes(q.toLowerCase())"
                                class="paper-row cursor-pointer"
                                onclick="window.location='{{ route('papers.show', $paper) }}'"
                            >
                                <td class="border-t border-notion-line px-4 py-3">
                                    <div class="flex items-center gap-2 font-medium">
                                        <span>{{ $paper->title }}</span>
                                        @if($paper->drive())
                                            <a href="{{ $paper->drive()->openUrl() }}" target="_blank" rel="noopener" onclick="event.stopPropagation()" class="rounded-full bg-[#e8f0fe] px-2 py-0.5 text-[11px] font-semibold text-[#1967d2] hover:bg-[#d2e3fc]">Drive</a>
                                        @endif
                                    </div>
                                </td>
                                <td class="border-t border-notion-line px-3 py-3 text-notion-muted">{{ $paper->group_name ?: '—' }}</td>
                                @if(auth()->user()->isTeacher())
                                    <td class="border-t border-notion-line px-3 py-3">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#efe6f6] px-2 py-0.5 text-xs">
                                            <span class="grid h-4 w-4 place-items-center rounded-full bg-[#7c3aed] text-[8px] text-white">{{ $paper->student->initials() }}</span>
                                            {{ $paper->student->name }}
                                        </span>
                                    </td>
                                @endif
                                <td class="border-t border-notion-line px-3 py-3"><x-status-badge :status="$paper->status" /></td>
                                <td class="border-t border-notion-line px-3 py-3">
                                    @foreach($paper->tagList() as $tag)
                                        <span class="mr-1 rounded-full bg-[#efe6f6] px-2 py-0.5 text-xs">{{ $tag }}</span>
                                    @endforeach
                                    @if($paper->tagList() === []) <span class="text-notion-faint">—</span> @endif
                                </td>
                                <td class="border-t border-notion-line px-3 py-3 {{ $paper->isOverdue() ? 'font-medium text-ember' : 'text-notion-muted' }}">
                                    {{ $paper->due_at?->format('M j') ?: '—' }}
                                </td>
                                <td class="border-t border-notion-line px-3 py-3 text-notion-muted">{{ $paper->score !== null ? $paper->score : '—' }}</td>
                                <td class="border-t border-notion-line px-4 py-3 text-notion-muted">{{ $paper->submitted_at?->format('M j') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $colspan }}" class="px-4 py-16 text-center">
                                    <div class="font-display text-2xl">Empty stage</div>
                                    <p class="mt-2 text-sm text-notion-faint">{{ $archived ? 'Nothing archived yet.' : 'Drop the first manuscript into the studio.' }}</p>
                                    @if(auth()->user()->isStudent() && ! $archived)
                                        <a href="{{ route('papers.create') }}" class="btn-primary mt-5">New paper</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <div x-show="view === 'board'" x-cloak class="mt-4 flex snap-x snap-mandatory gap-4 overflow-x-auto pb-4 md:snap-none">
            @foreach($columns as $column)
                <section class="w-[85vw] shrink-0 snap-center rounded-3xl bg-notion-board/80 p-3 sm:w-[270px]">
                    <div class="mb-3 flex items-center gap-2 px-1 text-sm">
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $column['color'] }}">{{ $column['label'] }}</span>
                        <span class="text-xs text-notion-faint">{{ $column['items']->count() }}</span>
                    </div>
                    <div class="space-y-2">
                        @forelse($column['items'] as $paper)
                            @php($haystack = strtolower($paper->title.' '.$paper->group_name.' '.$paper->student->name.' '.$paper->tags))
                            <div x-show="{{ json_encode($haystack) }}.includes(q.toLowerCase())" class="rounded-2xl bg-white p-3 shadow-card transition hover:-translate-y-0.5">
                                <div class="flex items-start justify-between gap-2">
                                    <a href="{{ route('papers.show', $paper) }}" class="text-sm font-medium hover:underline">{{ $paper->title }}</a>
                                    @if($paper->drive())
                                        <a href="{{ $paper->drive()->openUrl() }}" target="_blank" rel="noopener" class="shrink-0 rounded-full bg-[#e8f0fe] px-2 py-0.5 text-[11px] font-semibold text-[#1967d2]">Drive</a>
                                    @endif
                                </div>
                                <div class="mt-3 flex items-center gap-2 text-xs text-notion-faint">
                                    <span class="grid h-5 w-5 place-items-center rounded-full bg-[#7c3aed] text-[8px] text-white">{{ $paper->student->initials() }}</span>
                                    {{ $paper->group_name ?: $paper->student->name }}
                                </div>
                            </div>
                        @empty
                            <div class="px-1 py-8 text-center text-xs text-notion-faint">No pages</div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</x-app-layout>
