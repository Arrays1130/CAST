<x-guest-layout
    :title="$title ?? 'Change password · CAST'"
    :panel-title="$panelTitle ?? 'Set your own password.'"
    :panel-copy="$panelCopy ?? 'Choose a new password to continue.'"
>
    <p class="inline-flex rounded-full border border-ember/25 bg-ember/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-ember">First login</p>
    <h1 class="mt-4 font-display text-4xl leading-none tracking-tight">Change password</h1>
    <p class="mt-3 text-sm leading-relaxed text-notion-muted">
        Logged in as <span class="font-medium text-ink">{{ auth()->user()->email }}</span>.
        Replace the temporary password your adviser gave you.
    </p>

    <form method="POST" action="{{ route('password.change.update') }}" class="mt-8 space-y-4">
        @csrf
        @method('PUT')
        <div>
            <x-input-label for="password" value="New password" />
            <x-text-input id="password" class="block w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="password_confirmation" value="Repeat new password" />
            <x-text-input id="password_confirmation" class="block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
        </div>
        <x-primary-button class="w-full">Save and continue</x-primary-button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <button type="submit" class="text-sm font-medium text-notion-muted hover:text-ink">Log out</button>
    </form>
</x-guest-layout>
