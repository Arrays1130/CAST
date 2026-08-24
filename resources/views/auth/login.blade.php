<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <h1 class="font-display text-4xl">Welcome back</h1>
    <p class="mt-2 text-sm text-notion-muted">Continue to the studio.</p>
    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-4">
        @csrf
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="block w-full" type="password" name="password" required />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>
        <label class="flex items-center gap-2 text-sm text-notion-muted">
            <input type="checkbox" name="remember" class="rounded border-notion-line text-ember focus:ring-ember">
            Keep me logged in
        </label>
        <x-primary-button class="w-full">Enter studio</x-primary-button>
    </form>
    <p class="mt-5 text-sm text-notion-muted">Need an account? <a class="font-medium text-ember" href="{{ route('register') }}">Sign up</a></p>
</x-guest-layout>
