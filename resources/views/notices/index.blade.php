<x-app-layout>
    <div class="mx-auto max-w-2xl px-4 py-10 sm:px-6">
        <div class="flex items-center justify-between gap-3">
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
        <div class="surface mt-8 divide-y divide-notion-line">
            @forelse($notices as $notice)
                <form method="POST" action="{{ route('notices.read', $notice) }}">
                    @csrf
                    <button type="submit" class="flex w-full items-start justify-between gap-4 px-4 py-4 text-left transition hover:bg-paper/80">
                        <div>
                            <div class="text-sm {{ $notice->read_at ? 'text-notion-muted' : 'font-medium' }}">{{ $notice->message }}</div>
                            <div class="mt-1 text-xs text-notion-faint">{{ $notice->created_at->diffForHumans() }}</div>
                        </div>
                        @unless($notice->read_at)
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-ember shadow-glow"></span>
                        @endunless
                    </button>
                </form>
            @empty
                <p class="px-4 py-16 text-center text-sm text-notion-faint">Quiet for now. New work will land here.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $notices->links() }}</div>
    </div>
</x-app-layout>
