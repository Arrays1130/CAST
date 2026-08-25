<x-guest-layout
    title="Reset password · CAST"
    panel-title="Choose a new password."
    panel-copy="Use a strong password you have not reused on other school accounts."
>
    <p class="inline-flex rounded-full border border-notion-line bg-white px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-notion-muted">Account recovery</p>
    <h1 class="mt-4 font-display text-4xl leading-none tracking-tight">Reset password</h1>
    <p class="mt-3 text-sm leading-relaxed text-notion-muted">Set a new password, then log in through the Student or Adviser portal.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="password" value="New password" />
            <x-text-input id="password" class="block w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirm password" />
            <x-text-input id="password_confirmation" class="block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <x-primary-button class="w-full">Save new password</x-primary-button>
    </form>
</x-guest-layout>
