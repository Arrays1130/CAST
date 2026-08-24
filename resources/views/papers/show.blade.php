<x-app-layout>
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('papers.index') }}" class="text-sm text-notion-faint hover:text-notion-text">← Papers</a>
            <form method="POST" action="{{ route('papers.archive', $paper) }}">
                @csrf
                <button class="btn-secondary">{{ $paper->isArchived() ? 'Restore' : 'Archive' }}</button>
            </form>
        </div>

        @if($paper->isArchived())
            <p class="mt-6 inline-flex rounded-full bg-[#fff1e6] px-3 py-1 text-xs font-medium text-ember">In the archive</p>
        @endif
        <p class="mt-6 text-xs font-semibold uppercase tracking-[0.22em] text-ember">Manuscript</p>
        <h1 class="n-page-title mt-2">{{ $paper->title }}</h1>

        <form method="POST" action="{{ route('papers.update', $paper) }}" class="surface mt-8 p-5">
            @csrf
            @method('PUT')
            <div class="divide-y divide-notion-line text-sm">
                <div class="prop-row">
                    <div class="text-notion-faint">Title</div>
                    <x-text-input name="title" class="!mt-0 border-0 bg-transparent px-0 shadow-none focus:ring-0" :value="old('title', $paper->title)" required />
                </div>
                <div class="prop-row">
                    <div class="text-notion-faint">Status</div>
                    <div><x-status-badge :status="$paper->status" /></div>
                </div>
                <div class="prop-row">
                    <div class="text-notion-faint">Student</div>
                    <div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#efe6f6] px-2 py-0.5 text-xs">
                            <span class="grid h-5 w-5 place-items-center rounded-full bg-[#7c3aed] text-[9px] text-white">{{ $paper->student->initials() }}</span>
                            {{ $paper->student->name }}
                        </span>
                    </div>
                </div>
                <div class="prop-row">
                    <div class="text-notion-faint">Group</div>
                    <x-text-input name="group_name" class="!mt-0 border-0 bg-transparent px-0 shadow-none focus:ring-0" :value="old('group_name', $paper->group_name)" placeholder="Empty" />
                </div>
                <div class="prop-row">
                    <div class="text-notion-faint">Tags</div>
                    <x-text-input name="tags" class="!mt-0 border-0 bg-transparent px-0 shadow-none focus:ring-0" :value="old('tags', $paper->tags)" placeholder="chapter 1, draft" />
                </div>
                <div class="prop-row">
                    <div class="text-notion-faint">Due</div>
                    <input type="date" name="due_at" value="{{ old('due_at', optional($paper->due_at)->format('Y-m-d')) }}" class="field !mt-0 border-0 bg-transparent px-0 shadow-none focus:ring-0">
                </div>
                <div class="prop-row">
                    <div class="text-notion-faint">Files</div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span>{{ $paper->sourceLabel() }}</span>
                        @if($paper->hasLocalFile())
                            <a href="{{ route('papers.download', $paper) }}" class="text-ember hover:underline">Download</a>
                        @endif
                        @if($paper->drive())
                            <a href="{{ $paper->drive()->openUrl() }}" target="_blank" rel="noopener" class="btn-primary w-full sm:w-auto">Open file in Drive</a>
                        @endif
                    </div>
                </div>
                <div class="prop-row">
                    <div class="text-notion-faint">Submitted</div>
                    <div>{{ $paper->submitted_at?->format('F j, Y') }}</div>
                </div>
                @if(auth()->user()->isTeacher())
                    <div class="prop-row">
                        <div class="text-notion-faint">Score</div>
                        <input type="number" min="0" max="100" name="score" value="{{ old('score', $paper->score) }}" class="field !mt-0 w-24 border-0 bg-transparent px-0 shadow-none focus:ring-0" placeholder="0–100">
                    </div>
                    <div class="prop-row">
                        <div class="pt-2 text-notion-faint">Remarks</div>
                        <textarea name="remarks" rows="2" class="field !mt-0 border-0 bg-transparent px-0 shadow-none focus:ring-0" placeholder="Adviser notes">{{ old('remarks', $paper->remarks) }}</textarea>
                    </div>
                @elseif($paper->score !== null)
                    <div class="prop-row">
                        <div class="text-notion-faint">Score</div>
                        <div class="font-display text-xl">{{ $paper->score }} <span class="text-sm text-notion-faint">/ 100</span></div>
                    </div>
                    @if($paper->remarks)
                        <div class="prop-row">
                            <div class="text-notion-faint">Remarks</div>
                            <div class="whitespace-pre-wrap">{{ $paper->remarks }}</div>
                        </div>
                    @endif
                @endif
            </div>
            <div class="mt-4 flex justify-end">
                <x-primary-button>Save properties</x-primary-button>
            </div>
        </form>

        @if(auth()->user()->isTeacher())
            <div class="mt-4 flex flex-wrap gap-1.5">
                <form method="POST" action="{{ route('papers.status.update', $paper) }}" class="contents">
                    @csrf
                    @method('PUT')
                    @foreach($statuses as $status)
                        <button name="status" value="{{ $status->value }}" class="min-h-11 rounded-full px-3 py-2 text-xs transition {{ $paper->status === $status ? $status->badge().' ring-2 ring-ink/10' : 'bg-white/70 text-notion-muted hover:bg-white' }}">
                            {{ $status->label() }}
                        </button>
                    @endforeach
                </form>
            </div>
        @endif

        @if($paper->drive()?->previewUrl())
            <div class="mt-8 overflow-hidden rounded-3xl border border-notion-line shadow-card">
                <div class="flex items-center justify-between bg-ink px-4 py-2 text-sm text-white">
                    <span>Google Drive preview</span>
                    <a href="{{ $paper->drive()->openUrl() }}" target="_blank" rel="noopener" class="rounded-full bg-white/10 px-3 py-1 text-xs font-medium hover:bg-white/20">Open file</a>
                </div>
                <iframe title="Google Drive preview" src="{{ $paper->drive()->previewUrl() }}" class="h-64 w-full bg-white sm:h-[480px]"></iframe>
            </div>
        @endif

        @if(auth()->user()->isStudent() && auth()->id() === $paper->user_id)
            <div class="surface mt-8 p-5">
                <h2 class="font-display text-2xl">New version</h2>
                <p class="mt-1 text-sm text-notion-muted">The current file is kept in version history.</p>
                <form method="POST" action="{{ route('papers.file.update', $paper) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                    @csrf
                    @method('PUT')
                    <input type="file" name="file" accept=".pdf,.doc,.docx,application/pdf" class="field">
                    <x-text-input name="drive_url" :value="old('drive_url', $paper->drive_url)" placeholder="Google Drive link" />
                    @if($paper->drive())
                        <a href="{{ $paper->drive()->openUrl() }}" target="_blank" rel="noopener" class="inline-block text-sm font-medium text-ember hover:underline">Open current Drive file</a>
                    @endif
                    <x-input-error :messages="$errors->get('file')" />
                    <x-input-error :messages="$errors->get('drive_url')" />
                    <x-primary-button>Upload version</x-primary-button>
                </form>
            </div>
        @endif

        @if($paper->versions->isNotEmpty())
            <div class="mt-10">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-notion-faint">Version history</h2>
                <ul class="surface mt-3 divide-y divide-notion-line px-4 text-sm">
                    @foreach($paper->versions as $version)
                        <li class="flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <span>{{ $version->original_filename ?: 'Previous version' }} · {{ $version->created_at->diffForHumans() }}</span>
                            @if(filled($version->file_path))
                                <a class="text-ember" href="{{ route('papers.versions.download', [$paper, $version]) }}">Download</a>
                            @elseif($version->drive_url)
                                <a class="text-ember" href="{{ $version->drive_url }}" target="_blank" rel="noopener">Drive</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="surface mt-10 p-5">
            <h2 class="font-display text-2xl">Comments</h2>
            <form method="POST" action="{{ route('papers.comments.store', $paper) }}" class="mt-4">
                @csrf
                <textarea name="body" rows="3" required class="field" placeholder="Leave a note for the other side of the table…">{{ old('body') }}</textarea>
                <x-input-error :messages="$errors->get('body')" class="mt-1" />
                <div class="mt-3 flex justify-end">
                    <x-primary-button>Send</x-primary-button>
                </div>
            </form>
            <div class="mt-6 space-y-4">
                @forelse($paper->comments as $comment)
                    <article class="flex gap-3">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-ink text-[11px] font-bold text-white">{{ $comment->author->initials() }}</span>
                        <div>
                            <div class="text-sm">
                                <span class="font-medium">{{ $comment->author->name }}</span>
                                <span class="text-notion-faint"> · {{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="mt-0.5 whitespace-pre-wrap text-sm">{{ $comment->body }}</p>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-notion-faint">No comments yet. Open the conversation.</p>
                @endforelse
            </div>
        </div>

        @if($paper->activities->isNotEmpty())
            <div class="mt-10">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-notion-faint">Activity</h2>
                <ul class="relative mt-4 space-y-3 pl-5 text-sm text-notion-muted timeline">
                    @foreach($paper->activities as $activity)
                        <li class="relative">
                            <span class="absolute -left-5 top-1.5 h-2 w-2 rounded-full bg-ember"></span>
                            {{ $activity->actor->name }} {{ lcfirst($activity->message) }} · {{ $activity->created_at->diffForHumans() }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-app-layout>
