<x-guest-layout>
    <h1 class="font-display text-4xl">Join the studio</h1>
    <p class="mt-2 text-sm text-notion-muted">Students only. Advisers are invited.</p>
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
        <x-primary-button class="w-full">Create account</x-primary-button>
    </form>
    <p class="mt-5 text-sm text-notion-muted">Already have an account? <a class="font-medium text-ember" href="{{ route('login') }}">Log in</a></p>
</x-guest-layout>
