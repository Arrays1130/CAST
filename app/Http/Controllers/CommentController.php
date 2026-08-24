<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Paper;
use App\Services\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Paper $paper): RedirectResponse
    {
        $this->authorize('comment', $paper);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        Comment::create([
            'paper_id' => $paper->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        Workspace::log($paper, $request->user(), 'Left a comment.');

        if ($request->user()->isTeacher()) {
            Workspace::notify($paper->student, $paper, $request->user()->name.' commented on “'.$paper->title.'”.');
        } else {
            Workspace::notifyTeachers($paper, $request->user()->name.' commented on “'.$paper->title.'”.');
        }

        return back()->with('status', 'Comment posted.');
    }
}
