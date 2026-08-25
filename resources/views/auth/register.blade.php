<x-guest-layout>
    <h1 class="font-display text-4xl">Join the studio</h1>
    <p class="mt-2 text-sm text-notion-muted">Students self-register. Advisers need an invite code from your school.</p>
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
    <p class="mt-5 text-sm text-notion-muted">Already have an account? <a class="font-medium text-ember" href="{{ route('login') }}">Log in</a></p>
</x-guest-layout>
