<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    /**
     * XML sitemap listing every indexable URL.
     *
     * Search-engine-only URLs (the admin, the search page, confirm and
     * unsubscribe links) are deliberately absent — they are also blocked in
     * robots.txt and carry a noindex tag.
     */
    public function index(): Response
    {
        $urls = [];

        // Homepage — changes whenever a story is published.
        $newestPublishedAt = Article::query()->published()->max('published_at');

        $urls[] = [
            'loc' => route('home'),
            'lastmod' => $newestPublishedAt ? $this->w3c($newestPublishedAt) : null,
            'changefreq' => 'hourly',
            'priority' => '1.0',
        ];

        // Section archives.
        foreach (Category::query()->withMax('articles', 'published_at')->orderBy('name')->get() as $category) {
            $urls[] = [
                'loc' => route('category.show', $category),
                'lastmod' => $category->articles_max_published_at ? $this->w3c($category->articles_max_published_at) : null,
                'changefreq' => 'daily',
                'priority' => '0.8',
            ];
        }

        // Every published story.
        Article::query()
            ->published()
            ->latestFirst()
            ->select(['slug', 'published_at', 'updated_at'])
            ->chunk(500, function ($articles) use (&$urls) {
                foreach ($articles as $article) {
                    $urls[] = [
                        'loc' => route('article.show', $article),
                        'lastmod' => $this->w3c($article->updated_at ?? $article->published_at),
                        'changefreq' => 'weekly',
                        'priority' => '0.7',
                    ];
                }
            });

        // Author profiles. Only bylines with published work — an empty profile
        // is a thin page, and AuthorController 404s it anyway.
        $authors = Author::query()
            ->withMax(['articles as last_published_at' => fn ($query) => $query->published()], 'published_at')
            // whereHas rather than having() on a withCount alias — the latter
            // needs a GROUP BY and is rejected by SQLite.
            ->whereHas('articles', fn ($query) => $query->published())
            ->orderBy('name')
            ->get();

        $urls[] = [
            'loc' => route('authors.index'),
            'lastmod' => null,
            'changefreq' => 'weekly',
            'priority' => '0.5',
        ];

        foreach ($authors as $author) {
            $urls[] = [
                'loc' => route('authors.show', $author),
                'lastmod' => $author->last_published_at ? $this->w3c($author->last_published_at) : null,
                'changefreq' => 'weekly',
                'priority' => '0.5',
            ];
        }

        // Static pages.
        foreach (['about', 'team', 'contact', 'editorial-policy', 'advertise', 'privacy-policy', 'cookie-policy', 'terms'] as $name) {
            $urls[] = [
                'loc' => route($name),
                'lastmod' => null,
                'changefreq' => 'yearly',
                'priority' => '0.3',
            ];
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Sitemaps require W3C datetime format.
     */
    private function w3c(mixed $date): string
    {
        return Carbon::parse($date)->toAtomString();
    }
}
