<x-guest-layout
    title="Verify email · CAST"
    panel-title="One more step."
    panel-copy="Confirm your email so CAST knows this workspace belongs to you."
>
    <p class="inline-flex rounded-full border border-ember/25 bg-ember/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-ember">Email verification</p>
    <h1 class="mt-4 font-display text-4xl leading-none tracking-tight">Check your inbox</h1>
    <p class="mt-3 text-sm leading-relaxed text-notion-muted">
        We sent a <span class="font-medium text-ink">button / link</span> to
        <span class="font-medium text-ink">{{ auth()->user()->email }}</span>
        — not a 6-digit code. Open the email, then tap <span class="font-medium text-ink">Verify email address</span>.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800">
            New link sent. Check Gmail → Spam / Promotions if it is not in Primary. It can take 1–2 minutes on the free host.
        </div>
    @endif

    <x-input-error :messages="$errors->get('email')" class="mt-4" />

    <div class="mt-6 space-y-2 rounded-xl border border-notion-line bg-white px-3 py-3 text-xs leading-relaxed text-notion-muted">
        <p>1. Search Gmail for <span class="font-medium text-ink">CAST Studio</span> or the From address of your school Gmail.</p>
        <p>2. If it is in Spam, open it and choose <span class="font-medium text-ink">Not spam</span>.</p>
        <p>3. Still nothing? Ask an adviser to open <span class="font-medium text-ink">Students</span> and tap <span class="font-medium text-ink">Confirm email</span>.</p>
    </div>

    <div class="mt-8 flex items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>Resend verification email</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-medium text-notion-muted hover:text-ink">Log out</button>
        </form>
    </div>
</x-guest-layout>
