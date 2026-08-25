<x-guest-layout
    :title="$title ?? 'Forgot password · CAST'"
    :panel-title="$panelTitle ?? 'Reset your access.'"
    :panel-copy="$panelCopy ?? 'We will email a secure link so you can set a new password.'"
>
    @php($portal = $portal ?? null)
    @php($loginRoute = $loginRoute ?? 'login')

    <p class="inline-flex rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]
        {{ $portal === 'adviser' ? 'border-ink/15 bg-ink text-white' : ($portal === 'student' ? 'border-ember/25 bg-ember/10 text-ember' : 'border-notion-line bg-white text-notion-muted') }}">
        {{ $portal === 'adviser' ? 'Adviser recovery' : ($portal === 'student' ? 'Student recovery' : 'Account recovery') }}
    </p>
    <h1 class="mt-4 font-display text-4xl leading-none tracking-tight">Forgot password?</h1>
    <p class="mt-3 text-sm leading-relaxed text-notion-muted">Enter the email for your CAST account. We’ll send a reset link if it exists.</p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-4">
        @csrf
        @if ($portal)
            <input type="hidden" name="portal" value="{{ $portal }}">
        @endif
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>
        <x-primary-button class="w-full">Email password reset link</x-primary-button>
    </form>

    <p class="mt-6 text-sm text-notion-muted">
        Remembered it?
        <a class="font-medium text-ember" href="{{ route($loginRoute) }}">Back to log in</a>
    </p>
</x-guest-layout>
