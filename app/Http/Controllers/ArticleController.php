<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Contracts\View\View;

class ArticleController extends Controller
{
    /**
     * A single story. Records the view and shows related stories from the
     * same section underneath.
     */
    public function show(Article $article): View
    {
        // Route model binding resolves any article by slug, including drafts and
        // embargoed posts, so the publish state is enforced here.
        abort_unless($article->isPublished(), 404);

        $article->load(['category', 'author']);

        $article->recordView();

        $related = Article::query()
            ->published()
            ->with(['category', 'author'])
            ->where('category_id', $article->category_id)
            ->whereKeyNot($article->getKey())
            ->latestFirst()
            ->take(3)
            ->get();

        return view('article', [
            'article' => $article,
            // Only approved comments ever reach the page.
            'comments' => $article->comments()
                ->approved()
                ->orderBy('approved_at')
                ->orderBy('id')
                ->get(),
            'related' => $related,
            'popular' => Article::query()
                ->published()
                ->with('category')
                ->popular()
                ->take(5)
                ->get(),
        ]);
    }
}
