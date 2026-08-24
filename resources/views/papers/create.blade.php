<x-app-layout>
    <div
        class="fixed inset-0 z-30 flex items-center justify-center bg-ink/55 p-4 backdrop-blur-[2px]"
        x-data="uploadModal({
            source: '{{ old('drive_url') ? 'drive' : 'file' }}',
            title: {{ \Illuminate\Support\Js::from(old('title', '')) }},
            driveUrl: {{ \Illuminate\Support\Js::from(old('drive_url', '')) }},
        })"
    >
        <form method="POST" action="{{ route('papers.store') }}" enctype="multipart/form-data" class="w-full max-w-[520px] overflow-hidden rounded-2xl bg-white shadow-notion" @submit="busy = true">
            @csrf
            <div class="flex items-center justify-between border-b border-notion-line px-5 py-4">
                <h1 class="text-lg font-semibold">Upload Document</h1>
                <a href="{{ route('papers.index') }}" class="grid h-8 w-8 place-items-center rounded-lg text-lg text-notion-faint hover:bg-notion-hover" aria-label="Close">×</a>
            </div>

            <div class="space-y-4 px-5 py-5">
                <div>
                    <label class="text-[11px] font-semibold uppercase tracking-wide text-notion-faint">Document type (optional)</label>
                    <select name="doc_type" class="field">
                        <option value="">General Attachment</option>
                        <option value="Chapter 1" @selected(old('doc_type') === 'Chapter 1')>Chapter 1</option>
                        <option value="Chapter 2" @selected(old('doc_type') === 'Chapter 2')>Chapter 2</option>
                        <option value="Chapter 3" @selected(old('doc_type') === 'Chapter 3')>Chapter 3</option>
                        <option value="Full manuscript" @selected(old('doc_type') === 'Full manuscript')>Full manuscript</option>
                        <option value="Proposal" @selected(old('doc_type') === 'Proposal')>Proposal</option>
                    </select>
                </div>

                <div class="flex gap-1 rounded-full bg-paper p-1 text-xs">
                    <button type="button" @click="source = 'file'" class="flex-1 rounded-full py-1.5" :class="source === 'file' ? 'bg-white font-medium shadow-sm' : 'text-notion-muted'">Computer</button>
                    <button type="button" @click="openGoogleDrive()" class="flex-1 rounded-full py-1.5" :class="source === 'drive' ? 'bg-white font-medium shadow-sm' : 'text-notion-muted'">Google Drive</button>
                </div>

                <div x-show="source === 'file'">
                    <label class="drop-zone" :class="over ? 'is-over' : ''"
                        @dragover.prevent="over = true"
                        @dragleave.prevent="over = false"
                        @drop.prevent="over = false; setFile($event.dataTransfer.files[0])"
                    >
                        <input x-ref="input" id="file" name="file" type="file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="sr-only" @change="setFile($event.target.files[0])">
                        <span class="grid h-14 w-14 place-items-center rounded-full bg-[#7c3aed] text-2xl text-white shadow-lg">↑</span>
                        <span class="text-base font-medium text-ink" x-text="name ? name : 'Click to upload or drag and drop'"></span>
                        <span class="text-xs text-notion-faint">PDF, DOC, DOCX (max. 20MB)</span>
                    </label>
                    <x-input-error :messages="$errors->get('file')" class="mt-2" />
                </div>

                <div x-show="source === 'drive'" x-cloak class="space-y-3">
                    <p class="text-sm text-notion-muted">Pick the file in Drive, set share to <span class="font-medium text-ink">Anyone with the link</span>, then paste the link.</p>
                    <x-text-input id="drive_url" name="drive_url" x-model="driveUrl" :value="old('drive_url')" placeholder="Paste Google Drive share link" />
                    <a x-show="fileHref()" x-cloak :href="fileHref()" target="_blank" rel="noopener" class="text-sm font-medium text-[#1967d2] hover:underline">Open selected file</a>
                    <x-input-error :messages="$errors->get('drive_url')" />
                </div>

                <div>
                    <label for="title" class="text-[11px] font-semibold uppercase tracking-wide text-notion-faint">Display name / label</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-notion-faint">🏷</span>
                        <input id="title" name="title" x-model="title" type="text" class="field pl-9" placeholder="e.g. Chapter 1 - Draft">
                    </div>
                    <x-input-error :messages="$errors->get('title')" class="mt-1" />
                </div>

                <details class="text-sm">
                    <summary class="cursor-pointer text-notion-faint hover:text-ink">More details</summary>
                    <div class="mt-3 space-y-3">
                        <x-text-input name="group_name" :value="old('group_name')" placeholder="Group name" />
                        <x-text-input name="tags" :value="old('tags')" placeholder="Tags (chapter 1, draft)" />
                        <input type="date" name="due_at" value="{{ old('due_at') }}" class="field">
                    </div>
                </details>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-notion-line bg-[#fafafa] px-5 py-3">
                <a href="{{ route('papers.index') }}" class="rounded-lg border border-notion-line bg-white px-4 py-2 text-sm font-medium">Cancel</a>
                <button type="submit" class="rounded-lg bg-[#7c3aed] px-4 py-2 text-sm font-semibold text-white hover:bg-[#6d28d9]" x-bind:disabled="busy">
                    <span x-show="!busy">Upload</span>
                    <span x-show="busy" x-cloak>Uploading…</span>
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
