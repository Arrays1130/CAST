<x-app-layout>
    <div class="mx-auto max-w-2xl px-4 py-10 sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-ember">Inbox</p>
                <h1 class="n-page-title mt-2">Updates</h1>
            </div>
            <form method="POST" action="{{ route('notices.read-all') }}">
                @csrf
                <button class="btn-secondary">Mark all read</button>
            </form>
        </div>
        <p class="mt-2 text-sm text-notion-muted">Status changes, comments, and new submissions.</p>

        <div class="mt-6 flex flex-wrap gap-1 rounded-2xl border border-notion-line bg-white/60 p-1 text-sm shadow-card">
            <a href="{{ route('notices.index') }}" class="inline-flex min-h-11 items-center rounded-xl px-3 py-1.5 {{ ! $unreadOnly ? 'bg-ink text-white' : 'text-notion-muted hover:bg-white' }}">All</a>
            <a href="{{ route('notices.index', ['unread' => 1]) }}" class="inline-flex min-h-11 items-center rounded-xl px-3 py-1.5 {{ $unreadOnly ? 'bg-ink text-white' : 'text-notion-muted hover:bg-white' }}">Unread</a>
        </div>

        <div class="surface mt-6 divide-y divide-notion-line">
            @forelse($notices as $notice)
                <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-paper px-2 py-0.5 text-[11px] font-medium text-notion-muted">{{ $notice->kindLabel() }}</span>
                            @unless($notice->read_at)
                                <span class="h-2 w-2 rounded-full bg-ember shadow-glow"></span>
                            @endunless
                        </div>
                        @if($notice->paper)
                            <div class="mt-1 text-xs font-medium text-notion-faint">{{ $notice->paper->title }}</div>
                        @endif
                        <div class="mt-1 text-sm {{ $notice->read_at ? 'text-notion-muted' : 'font-medium text-ink' }}">{{ $notice->message }}</div>
                        <div class="mt-1 text-xs text-notion-faint">{{ $notice->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="flex shrink-0 flex-wrap gap-2">
                        @if($notice->paper_id)
                            <form method="POST" action="{{ route('notices.read', $notice) }}">
                                @csrf
                                <button class="btn-primary">Open paper</button>
                            </form>
                        @endif
                        @unless($notice->read_at)
                            <form method="POST" action="{{ route('notices.read', $notice) }}">
                                @csrf
                                <input type="hidden" name="stay" value="1">
                                <button class="btn-secondary">Mark read</button>
                            </form>
                        @endunless
                    </div>
                </div>
            @empty
                <p class="px-4 py-16 text-center text-sm text-notion-faint">{{ $unreadOnly ? 'No unread updates.' : 'Quiet for now. New work will land here.' }}</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $notices->links() }}</div>
    </div>
</x-app-layout>
