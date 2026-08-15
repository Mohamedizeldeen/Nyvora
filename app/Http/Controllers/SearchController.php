<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Keyword search over headlines and excerpts, so the header's search icon
     * leads somewhere real.
     */
    public function index(Request $request): View
    {
        $term = trim((string) $request->query('q', ''));

        $articles = Article::query()
            ->published()
            ->with(['category', 'author'])
            ->when($term !== '', function ($query) use ($term) {
                // Neutralise the LIKE wildcards so searching for "%" or "_"
                // looks for those characters instead of matching everything.
                //
                // "!" is the escape character rather than the usual backslash
                // because MySQL and SQLite disagree about backslashes inside
                // string literals — "ESCAPE '!'" means the same thing to both.
                $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);
                $pattern = "%{$escaped}%";

                $query->where(fn ($query) => $query
                    ->whereRaw("title LIKE ? ESCAPE '!'", [$pattern])
                    ->orWhereRaw("excerpt LIKE ? ESCAPE '!'", [$pattern]));
            })
            ->latestFirst()
            ->paginate(10)
            ->withQueryString();

        return view('search', [
            'term' => $term,
            'articles' => $articles,
            'popular' => Article::query()
                ->published()
                ->with('category')
                ->popular()
                ->take(5)
                ->get(),
        ]);
    }
}
