<x-guest-layout
    :title="$title ?? 'Log in · CAST'"
    :panel-title="$panelTitle ?? 'Choose your studio entrance.'"
    :panel-copy="$panelCopy ?? 'Student and adviser accounts are separate. Pick the portal that matches your role.'"
>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <p class="inline-flex rounded-full border border-notion-line bg-white px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-notion-muted">Secure role portals</p>
    <h1 class="mt-4 font-display text-4xl leading-none tracking-tight">Who is logging in?</h1>
    <p class="mt-3 text-sm leading-relaxed text-notion-muted">Pick your role first so CAST opens the right workspace — and keeps student and adviser access separate.</p>

    <div class="mt-8 space-y-3">
        <a href="{{ route('login.student') }}" class="block rounded-2xl border border-notion-line bg-white p-4 transition hover:-translate-y-0.5 hover:border-ember/40 hover:shadow-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ember">Student</p>
                    <p class="mt-1 text-lg font-semibold text-ink">Student log in</p>
                    <p class="mt-1 text-sm text-notion-muted">Upload papers, read feedback, and track revisions.</p>
                </div>
                <span class="mt-1 text-lg text-notion-faint" aria-hidden="true">→</span>
            </div>
        </a>

        <a href="{{ route('login.adviser') }}" class="block rounded-2xl border border-notion-line bg-white p-4 transition hover:-translate-y-0.5 hover:border-ember/40 hover:shadow-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-notion-muted">Adviser</p>
                    <p class="mt-1 text-lg font-semibold text-ink">Adviser log in</p>
                    <p class="mt-1 text-sm text-notion-muted">Review queue, live PDF preview, scores, and comments.</p>
                </div>
                <span class="mt-1 text-lg text-notion-faint" aria-hidden="true">→</span>
            </div>
        </a>
    </div>

    <p class="mt-6 rounded-xl border border-notion-line bg-white/70 px-3 py-2 text-xs leading-relaxed text-notion-muted">
        Security tip: a student account cannot enter the adviser portal, and an adviser account cannot enter the student portal.
    </p>

    <p class="mt-5 text-sm text-notion-muted">New student? <a class="font-medium text-ember" href="{{ route('register') }}">Create an account</a></p>
</x-guest-layout>
