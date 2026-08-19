<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Contracts\View\View;

class AuthorController extends Controller
{
    /**
     * The byline index — everyone who writes for us.
     *
     * Only authors with at least one published story appear: an empty profile
     * is a thin page that helps nobody and dilutes crawl budget.
     */
    public function index(): View
    {
        return view('authors.index', [
            'authors' => Author::query()
                ->withCount(['articles' => fn ($query) => $query->published()])
                // whereHas, not having(): HAVING against a withCount subquery
                // alias without a GROUP BY is rejected by SQLite and by MySQL
                // in strict mode. This is portable and reads the same.
                ->whereHas('articles', fn ($query) => $query->published())
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * One author: their bio and everything they have published.
     */
    public function show(Author $author): View
    {
        $articles = $author->articles()
            ->published()
            ->with(['category', 'author'])
            ->latestFirst()
            ->paginate((int) setting('articles_per_page'));

        // A byline with nothing published yet should not be a public page.
        abort_if($articles->total() === 0, 404);

        return view('authors.show', [
            'author' => $author,
            'articles' => $articles,
            // Scoped to this author, the same way the category archive scopes
            // its sidebar — the page stays about the byline the reader chose.
            'popular' => $author->articles()
                ->published()
                ->with('category')
                ->popular()
                ->take(5)
                ->get(),
        ]);
    }
}
