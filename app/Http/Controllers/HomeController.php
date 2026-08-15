<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;

class HomeController extends Controller
{
    /**
     * The homepage: hero, "Latest News" feed and sidebar widgets.
     */
    public function index(): View
    {
        $hero = $this->heroArticles();

        // Headlines widget: the most-read stories, minus whatever is already
        // shown large in the hero so the same story never appears twice.
        $headlines = Article::query()
            ->published()
            ->with('category')
            ->whereNotIn('id', $hero->pluck('id'))
            ->popular()
            ->take(6)
            ->get();

        // "Latest News" — newest first, also excluding the hero stories.
        // The page size is set in Admin → Settings.
        $articles = Article::query()
            ->published()
            ->with(['category', 'author'])
            ->whereNotIn('id', $hero->pluck('id'))
            ->latestFirst()
            ->paginate((int) setting('articles_per_page'));

        return view('home', [
            'heroPrimary' => $hero->first(),
            'heroSecondary' => $hero->skip(1)->first(),
            'headlines' => $headlines,
            'articles' => $articles,
            'popular' => $this->popularArticles(),
        ]);
    }

    /**
     * The two stories that fill the hero.
     *
     * Featured articles come first; if fewer than two are flagged, the most
     * recent stories top the list up so the hero is never half-empty.
     *
     * @return Collection<int, Article>
     */
    private function heroArticles(): Collection
    {
        $featured = Article::query()
            ->published()
            ->with(['category', 'author'])
            ->where('is_featured', true)
            ->latestFirst()
            ->take(2)
            ->get();

        if ($featured->count() === 2) {
            return $featured;
        }

        return $featured->merge(
            Article::query()
                ->published()
                ->with(['category', 'author'])
                ->whereNotIn('id', $featured->pluck('id'))
                ->latestFirst()
                ->take(2 - $featured->count())
                ->get()
        );
    }

    /**
     * The five most-read stories, for the sidebar's "Most Popular" widget.
     *
     * @return Collection<int, Article>
     */
    private function popularArticles(): Collection
    {
        return Article::query()
            ->published()
            ->with('category')
            ->popular()
            ->take(5)
            ->get();
    }
}
