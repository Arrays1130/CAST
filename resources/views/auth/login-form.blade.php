<x-guest-layout
    :title="$title"
    :panel-title="$panelTitle"
    :panel-copy="$panelCopy"
>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="flex items-center justify-between gap-3">
        <p class="inline-flex rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]
            {{ $portal === 'adviser' ? 'border-ink/15 bg-ink text-white' : 'border-ember/25 bg-ember/10 text-ember' }}">
            {{ $portal === 'adviser' ? 'Adviser portal' : 'Student portal' }}
        </p>
        <a href="{{ route('login') }}" class="text-xs font-medium text-notion-muted hover:text-ink">All portals</a>
    </div>

    <h1 class="mt-4 font-display text-4xl leading-none tracking-tight">{{ $heading }}</h1>
    <p class="mt-3 text-sm leading-relaxed text-notion-muted">{{ $subheading }}</p>

    <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-4">
        @csrf
        <input type="hidden" name="role" value="{{ $role }}">

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="flex items-center justify-between gap-3">
            <label class="flex items-center gap-2 text-sm text-notion-muted">
                <input type="checkbox" name="remember" class="rounded border-notion-line text-ember focus:ring-ember">
                Keep me logged in
            </label>
            <a class="text-sm font-medium text-ember" href="{{ route('password.request', ['portal' => $portal === 'adviser' ? 'adviser' : 'student']) }}">Forgot?</a>
        </div>

        <x-primary-button class="w-full">{{ $cta }}</x-primary-button>
    </form>

    @if ($portal !== 'adviser')
        <p class="mt-4 text-xs leading-relaxed text-notion-muted">If your adviser created your account, use the temporary password they gave you. CAST will ask you to change it after login.</p>
    @endif

    <div class="mt-6 rounded-xl border border-notion-line bg-white px-3 py-3 text-sm">
        <p class="text-notion-muted">{{ $switchLabel }}</p>
        <a class="mt-1 inline-flex font-medium text-ember" href="{{ route($switchRoute) }}">{{ $switchText }} →</a>
    </div>

    @if ($showRegister)
        <p class="mt-5 text-sm text-notion-muted">Need an account? <a class="font-medium text-ember" href="{{ route('register') }}">Sign up as a student</a></p>
    @else
        <p class="mt-5 text-sm text-notion-muted">Need an adviser account? Ask your school for an invite code, then <a class="font-medium text-ember" href="{{ route('register') }}">register with it</a>.</p>
    @endif
</x-guest-layout>
