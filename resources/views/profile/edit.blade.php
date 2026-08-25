<x-app-layout>
    <div class="mx-auto max-w-2xl px-4 py-10 sm:px-6">
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-ember">Account</p>
        <h1 class="n-page-title mt-2">Settings</h1>
        <p class="mt-2 text-sm text-notion-muted">Your name, email, and password for this studio.</p>
        <div class="surface mt-8 space-y-8 p-6">
            <div>@include('profile.partials.update-profile-information-form')</div>
            <div class="border-t border-notion-line pt-8">@include('profile.partials.update-password-form')</div>
            @if(auth()->user()->isTeacher())
                <div class="border-t border-notion-line pt-8">@include('profile.partials.promote-teacher-form')</div>
            @endif
            <div class="border-t border-notion-line pt-8">@include('profile.partials.delete-user-form')</div>
        </div>
    </div>
</x-app-layout>
