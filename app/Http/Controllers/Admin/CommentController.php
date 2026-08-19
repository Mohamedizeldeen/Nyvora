<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Comment moderation.
 *
 * Nothing a reader writes appears on the site until it is approved here.
 */
class CommentController extends Controller
{
    public function index(Request $request): View
    {
        // Pending is the default view: it is the queue that needs action.
        $status = (string) $request->query('status', 'pending');

        return view('admin.comments.index', [
            'comments' => Comment::query()
                ->with('article:id,title,slug')
                ->when($status === 'pending', fn ($query) => $query->pending())
                ->when($status === 'approved', fn ($query) => $query->approved())
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(20)
                ->withQueryString(),
            'status' => $status,
            'pendingCount' => Comment::query()->pending()->count(),
            'approvedCount' => Comment::query()->approved()->count(),
        ]);
    }

    public function approve(Comment $comment): RedirectResponse
    {
        $comment->approve();

        return back()->with('status', 'Comment approved — it is now visible on the article.');
    }

    public function unapprove(Comment $comment): RedirectResponse
    {
        $comment->unapprove();

        return back()->with('status', 'Comment hidden — it is back in the queue.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('status', 'Comment deleted.');
    }
}
