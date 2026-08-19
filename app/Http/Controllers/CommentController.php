<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    /**
     * Take a comment from a reader and hold it for moderation.
     *
     * Nothing written here is visible to anyone else until an administrator
     * approves it, so the article page never shows unreviewed text.
     */
    public function store(CommentRequest $request, Article $article): RedirectResponse
    {
        // The form is hidden when comments are closed, but a stale page could
        // still post. 404 rather than 403: an unpublished story should not
        // even confirm it exists.
        abort_unless($article->isPublished(), 404);
        abort_unless($article->acceptsComments(), 403);

        $article->comments()->create([
            'name' => $request->validated('name'),
            'body' => $request->validated('body'),
            // approved_at stays null: a human decides.
        ]);

        return back()
            ->with('comment_posted', 'Thank you — your comment has been sent to our editors. It will appear here once it is approved.')
            ->withFragment('comments');
    }
}
