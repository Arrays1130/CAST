<div>
    <h2 class="font-display text-xl">Promote adviser</h2>
    <p class="mt-1 text-sm text-notion-muted">Turn an existing student account into an adviser. Share the invite code for new adviser signups.</p>
    @if(filled(config('cast.teacher_invite_code')))
        <p class="mt-2 rounded-xl bg-paper px-3 py-2 text-xs text-notion-muted">Invite code is set in server env (<code class="text-ink">TEACHER_INVITE_CODE</code>).</p>
    @else
        <p class="mt-2 rounded-xl bg-[#fff6ed] px-3 py-2 text-xs text-[#9a3412]">No invite code configured. Set <code>TEACHER_INVITE_CODE</code> on the server, or promote users below.</p>
    @endif
    <form method="POST" action="{{ route('teachers.promote') }}" class="mt-4 space-y-3">
        @csrf
        <div>
            <x-input-label for="promote_email" value="Student email" />
            <x-text-input id="promote_email" class="block w-full" type="email" name="email" :value="old('email')" required placeholder="student@school.edu" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>
        <x-primary-button>Make adviser</x-primary-button>
    </form>
</div>
