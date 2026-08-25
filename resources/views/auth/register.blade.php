<x-guest-layout
    title="Join CAST"
    panel-title="Students join freely."
    panel-copy="Create a student account in seconds. Advisers need an invite code from your school."
>
    <p class="inline-flex rounded-full border border-ember/25 bg-ember/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-ember">Student signup</p>
    <h1 class="mt-4 font-display text-4xl leading-none tracking-tight">Join the studio</h1>
    <p class="mt-3 text-sm leading-relaxed text-notion-muted">Default accounts are student. Use an adviser invite code only if your school gave you one.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
        @csrf
        <div>
            <x-input-label for="name" value="Name" />
            <x-text-input id="name" class="block w-full" type="text" name="name" :value="old('name')" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="block w-full" type="password" name="password" required />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="password_confirmation" value="Repeat password" />
            <x-text-input id="password_confirmation" class="block w-full" type="password" name="password_confirmation" required />
        </div>
        <div>
            <x-input-label for="invite_code" value="Adviser invite code (optional)" />
            <x-text-input id="invite_code" class="block w-full" type="text" name="invite_code" :value="old('invite_code')" autocomplete="off" />
            <p class="mt-1 text-xs text-notion-faint">Leave blank for a student account.</p>
            <x-input-error :messages="$errors->get('invite_code')" class="mt-1" />
        </div>
        <x-primary-button class="w-full">Create account</x-primary-button>
    </form>

    <div class="mt-6 space-y-2 text-sm text-notion-muted">
        <p>Already a student? <a class="font-medium text-ember" href="{{ route('login.student') }}">Student log in</a></p>
        <p>Already an adviser? <a class="font-medium text-ember" href="{{ route('login.adviser') }}">Adviser log in</a></p>
    </div>
</x-guest-layout>
