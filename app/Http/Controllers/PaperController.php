<?php

namespace App\Http\Controllers;

use App\Enums\PaperStatus;
use App\Models\Paper;
use App\Models\PaperVersion;
use App\Models\User;
use App\Services\ReferenceDetective;
use App\Services\Workspace;
use App\Support\DurableStorage;
use App\Support\GoogleDriveLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaperController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $archived = $request->boolean('archived');
        $filter = $request->string('filter')->toString();
        $allowedFilters = ['submitted', 'for_review', 'needs_revision', 'approved', 'overdue'];

        if (! in_array($filter, $allowedFilters, true)) {
            $filter = '';
        }

        $papers = Paper::query()
            ->with('student')
            ->withCount('comments')
            ->when(! $user->isTeacher(), fn ($query) => $query->where('user_id', $user->id))
            ->when($archived, fn ($query) => $query->whereNotNull('archived_at'), fn ($query) => $query->whereNull('archived_at'))
            ->latest('submitted_at')
            ->get();

        $stats = [
            'total' => $papers->count(),
            'submitted' => $papers->where('status', PaperStatus::Submitted)->count(),
            'for_review' => $papers->where('status', PaperStatus::ForReview)->count(),
            'needs_revision' => $papers->where('status', PaperStatus::NeedsRevision)->count(),
            'approved' => $papers->where('status', PaperStatus::Approved)->count(),
            'overdue' => $papers->filter->isOverdue()->count(),
        ];

        if ($user->isTeacher()) {
            $papers = $papers
                ->sortBy([
                    fn (Paper $paper) => $paper->isOverdue() ? 0 : 1,
                    fn (Paper $paper) => -($paper->submitted_at?->getTimestamp() ?? 0),
                ])
                ->values();
        }

        if ($filter === 'overdue') {
            $papers = $papers->filter->isOverdue()->values();
        } elseif ($filter !== '') {
            $status = PaperStatus::from($filter);
            $papers = $papers->where('status', $status)->values();
        }

        return view('papers.index', compact('papers', 'stats', 'archived', 'filter'));
    }

    public function create(): View
    {
        $this->authorize('create', Paper::class);

        return view('papers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Paper::class);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'doc_type' => ['nullable', 'string', 'max:80'],
            'group_name' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:255'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
            'drive_url' => ['nullable', 'url', 'max:500'],
        ]);

        if ($request->hasFile('file') && DurableStorage::requiresDriveInProduction()) {
            return back()->withErrors([
                'file' => 'Computer uploads are disabled on this host (files would disappear after restart). Use a Google Drive share link instead, or ask an admin to configure AWS_BUCKET / R2.',
            ])->withInput();
        }

        if (! $request->hasFile('file') && blank($validated['drive_url'] ?? null)) {
            return back()->withErrors([
                'file' => DurableStorage::requiresDriveInProduction()
                    ? 'Paste a Google Drive share link (Anyone with the link).'
                    : 'Upload a file or paste a Google Drive link.',
            ])->withInput();
        }

        if (filled($validated['drive_url'] ?? null) && ! GoogleDriveLink::parse($validated['drive_url'])) {
            return back()->withErrors([
                'drive_url' => 'Use a valid Google Drive, Docs, Slides, or Sheets share link.',
            ])->withInput();
        }

        $file = $request->file('file');
        $drive = GoogleDriveLink::parse($validated['drive_url'] ?? null);
        $title = $validated['title']
            ?: ($file ? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) : null)
            ?: ($drive?->label() ?? 'Untitled manuscript');
        $tags = $validated['tags'] ?: ($validated['doc_type'] ?? null);

        $paper = Paper::create([
            'user_id' => $request->user()->id,
            'title' => $title,
            'group_name' => $validated['group_name'] ?? null,
            'tags' => $tags,
            'due_at' => null,
            'status' => PaperStatus::Submitted,
            'file_path' => $file ? $file->store('', 'papers') : '',
            'original_filename' => $file?->getClientOriginalName() ?: ($drive?->label() ?? 'Google Drive'),
            'drive_url' => $drive?->openUrl(),
            'submitted_at' => now(),
        ]);

        Workspace::log($paper, $request->user(), 'Created this page and submitted a manuscript.');
        Workspace::notifyTeachers($paper, $request->user()->name.' submitted “'.$paper->title.'”.');

        return redirect()->route('papers.show', $paper)->with('status', 'Page added to Papers.');
    }

    public function show(Paper $paper): View
    {
        $this->authorize('view', $paper);
        $paper->load(['student', 'comments.author', 'versions', 'activities.actor']);

        return view('papers.show', [
            'paper' => $paper,
            'statuses' => PaperStatus::cases(),
        ]);
    }

    public function scanReferences(Request $request, Paper $paper, ReferenceDetective $detective): RedirectResponse
    {
        $this->authorize('view', $paper);

        $result = $detective->scan($paper);
        $paper->update([
            'reference_scan' => $result,
            'reference_scanned_at' => now(),
        ]);

        return back()->with('status', $result['status'] === 'ok'
            ? 'Reference Detective finished.'
            : 'Reference Detective could not fully scan this file.');
    }

    public function updateDetails(Request $request, Paper $paper): RedirectResponse
    {
        $this->authorize('updateDetails', $paper);

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'group_name' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:255'],
        ];

        if ($request->user()->isTeacher()) {
            $rules['due_at'] = ['nullable', 'date'];
            $rules['score'] = ['nullable', 'integer', 'min:0', 'max:100'];
            $rules['remarks'] = ['nullable', 'string', 'max:2000'];
        }

        $validated = $request->validate($rules);
        $paper->update($validated);
        Workspace::log($paper, $request->user(), 'Updated page properties.');

        return back()->with('status', 'Properties saved.');
    }

    public function updateFile(Request $request, Paper $paper): RedirectResponse
    {
        $this->authorize('updateFile', $paper);

        $validated = $request->validate([
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
            'drive_url' => ['nullable', 'url', 'max:500'],
        ]);

        if ($request->hasFile('file') && DurableStorage::requiresDriveInProduction()) {
            return back()->withErrors([
                'file' => 'Computer uploads are disabled on this host. Use a Google Drive share link instead.',
            ]);
        }

        if (! $request->hasFile('file') && blank($validated['drive_url'] ?? null)) {
            return back()->withErrors([
                'file' => DurableStorage::requiresDriveInProduction()
                    ? 'Paste a Google Drive share link.'
                    : 'Upload a new file or paste a Google Drive link.',
            ]);
        }

        if (filled($validated['drive_url'] ?? null) && ! GoogleDriveLink::parse($validated['drive_url'])) {
            return back()->withErrors([
                'drive_url' => 'Use a valid Google Drive, Docs, Slides, or Sheets share link.',
            ]);
        }

        Workspace::snapshot($paper);

        $payload = [
            'status' => PaperStatus::Submitted,
            'submitted_at' => now(),
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $payload['file_path'] = $file->store('', 'papers');
            $payload['original_filename'] = $file->getClientOriginalName();
        }

        if (filled($validated['drive_url'] ?? null)) {
            $drive = GoogleDriveLink::parse($validated['drive_url']);
            $payload['drive_url'] = $drive?->openUrl();
            if (! $request->hasFile('file')) {
                $payload['original_filename'] = $drive?->label() ?? 'Google Drive';
            }
        }

        $paper->update($payload);
        Workspace::log($paper, $request->user(), 'Uploaded a new manuscript version.');
        Workspace::notifyTeachers($paper, $request->user()->name.' resubmitted “'.$paper->title.'”.');

        return back()->with('status', 'New version saved. Status is Submitted.');
    }

    public function updateStatus(Request $request, Paper $paper): RedirectResponse
    {
        $this->authorize('updateStatus', $paper);

        $validated = $request->validate([
            'status' => ['required', 'in:submitted,for_review,needs_revision,approved'],
            'comment' => ['nullable', 'string', 'max:5000'],
        ]);

        $status = PaperStatus::from($validated['status']);
        $paper->update(['status' => $status]);
        Workspace::log($paper, $request->user(), 'Set status to '.$status->label().'.');
        Workspace::notify($paper->student, $paper, 'Status of “'.$paper->title.'” is now '.$status->label().'.');

        if (filled($validated['comment'] ?? null)) {
            $paper->comments()->create([
                'user_id' => $request->user()->id,
                'body' => $validated['comment'],
            ]);
            Workspace::log($paper, $request->user(), 'Left a comment.');
            Workspace::notify($paper->student, $paper, $request->user()->name.' commented on “'.$paper->title.'”.');
        }

        return back()->with('status', filled($validated['comment'] ?? null)
            ? 'Status updated and comment sent.'
            : 'Status updated.');
    }

    public function archive(Request $request, Paper $paper): RedirectResponse
    {
        $this->authorize('archive', $paper);

        $paper->update([
            'archived_at' => $paper->isArchived() ? null : now(),
        ]);
        Workspace::log($paper, $request->user(), $paper->isArchived() ? 'Archived this page.' : 'Restored this page.');

        return redirect()->route('papers.index', $paper->isArchived() ? ['archived' => 1] : [])
            ->with('status', $paper->isArchived() ? 'Moved to archive.' : 'Restored to Papers.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()->isTeacher(), 403);

        $filename = 'cast-papers-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Title', 'Student', 'Group', 'Status', 'Score', 'Tags', 'Due', 'Submitted']);

            Paper::query()
                ->with('student')
                ->when(! $request->boolean('archived'), fn ($query) => $query->whereNull('archived_at'))
                ->latest('submitted_at')
                ->get()
                ->each(function (Paper $paper) use ($handle) {
                    fputcsv($handle, [
                        $paper->title,
                        $paper->student->name,
                        $paper->group_name,
                        $paper->status->label(),
                        $paper->score,
                        $paper->tags,
                        optional($paper->due_at)->toDateString(),
                        optional($paper->submitted_at)->toDateTimeString(),
                    ]);
                });

            fclose($handle);
        }, $filename);
    }

    public function download(Paper $paper): StreamedResponse
    {
        $this->authorize('download', $paper);

        abort_unless($paper->hasLocalFile(), 404, 'No uploaded file on this paper. Open Google Drive instead.');

        return Storage::disk('papers')->download($paper->file_path, $paper->original_filename);
    }

    public function viewFile(Paper $paper): StreamedResponse
    {
        $this->authorize('download', $paper);

        abort_unless($paper->hasLocalFile() && $paper->isPdf(), 404, 'Only PDF uploads can be previewed here.');

        return Storage::disk('papers')->response(
            $paper->file_path,
            $paper->original_filename,
            ['Content-Type' => 'application/pdf'],
            'inline'
        );
    }

    public function downloadVersion(Paper $paper, PaperVersion $version): StreamedResponse
    {
        $this->authorize('download', $paper);
        abort_unless($version->paper_id === $paper->id && filled($version->file_path), 404);

        return Storage::disk('papers')->download($version->file_path, $version->original_filename ?: 'version');
    }
}
