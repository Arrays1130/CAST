<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function index(Request $request): View
    {
        $notices = $request->user()->notices()->with('paper')->paginate(30);

        return view('notices.index', compact('notices'));
    }

    public function markRead(Request $request, Notice $notice): RedirectResponse
    {
        abort_unless($notice->user_id === $request->user()->id, 403);

        $notice->update(['read_at' => now()]);

        return $notice->paper_id
            ? redirect()->route('papers.show', $notice->paper_id)
            : back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->notices()->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('status', 'All updates marked as read.');
    }
}
