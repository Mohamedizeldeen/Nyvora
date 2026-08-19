<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reports built from the site's own data.
 *
 * Deliberately not from the Google Analytics API: that needs a service account
 * and credentials, and would make this page fail whenever Google is slow or the
 * key expires. Everything here comes from our own tables, so it always works
 * and needs no setup. Google Analytics answers a different question — where
 * readers came from — and is linked to from the page.
 */
class ReportController extends Controller
{
    /** Ranges the page offers, in days. */
    private const RANGES = [7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days'];

    public function index(Request $request): View
    {
        $days = (int) $request->query('days', 30);
        $days = array_key_exists($days, self::RANGES) ? $days : 30;

        $from = now()->startOfDay()->subDays($days - 1);

        // Views per day, zero-filled so the chart has no gaps.
        $daily = DB::table('article_view_daily')
            ->selectRaw('viewed_on, SUM(views) as views')
            ->where('viewed_on', '>=', $from->toDateString())
            ->groupBy('viewed_on')
            ->pluck('views', 'viewed_on');

        $series = [];
        for ($date = $from->copy(); $date <= now()->startOfDay(); $date->addDay()) {
            $key = $date->toDateString();
            $series[] = ['date' => $date->copy(), 'views' => (int) ($daily[$key] ?? 0)];
        }

        $viewsInRange = array_sum(array_column($series, 'views'));

        // The same length of time immediately before, for a like-for-like change.
        $previousViews = (int) DB::table('article_view_daily')
            ->where('viewed_on', '>=', $from->copy()->subDays($days)->toDateString())
            ->where('viewed_on', '<', $from->toDateString())
            ->sum('views');

        return view('admin.reports', [
            'ranges' => self::RANGES,
            'days' => $days,
            'from' => $from,
            'series' => $series,
            'peak' => max(1, max(array_column($series, 'views') ?: [0])),
            'viewsInRange' => $viewsInRange,
            'previousViews' => $previousViews,
            'change' => $this->percentageChange($previousViews, $viewsInRange),

            'topArticles' => $this->topArticles($from),
            'byCategory' => $this->viewsByCategory($from),
            'byAuthor' => $this->viewsByAuthor($from),

            'publishedInRange' => Article::query()->published()->where('published_at', '>=', $from)->count(),
            'commentsInRange' => Comment::query()->where('created_at', '>=', $from)->count(),
            'pendingComments' => Comment::query()->pending()->count(),
            'messagesInRange' => ContactMessage::query()->where('created_at', '>=', $from)->count(),
            'unreadMessages' => ContactMessage::query()->unread()->count(),

            'lifetimeViews' => (int) Article::query()->sum('views_count'),
            'totalPublished' => Article::query()->published()->count(),
            'analyticsId' => analytics_id(),
        ]);
    }

    /**
     * Percentage change between two totals, or null when there is no baseline.
     */
    private function percentageChange(int $before, int $after): ?float
    {
        if ($before === 0) {
            return null;
        }

        return round((($after - $before) / $before) * 100, 1);
    }

    /**
     * @return Collection<int, object>
     */
    private function topArticles(Carbon $from)
    {
        return DB::table('article_view_daily')
            ->join('articles', 'articles.id', '=', 'article_view_daily.article_id')
            ->where('article_view_daily.viewed_on', '>=', $from->toDateString())
            ->groupBy('articles.id', 'articles.title', 'articles.slug')
            ->selectRaw('articles.id, articles.title, articles.slug, SUM(article_view_daily.views) as views')
            ->orderByDesc('views')
            ->limit(10)
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    private function viewsByCategory(Carbon $from)
    {
        return DB::table('article_view_daily')
            ->join('articles', 'articles.id', '=', 'article_view_daily.article_id')
            ->join('categories', 'categories.id', '=', 'articles.category_id')
            ->where('article_view_daily.viewed_on', '>=', $from->toDateString())
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->selectRaw('categories.name, categories.color, SUM(article_view_daily.views) as views')
            ->orderByDesc('views')
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    private function viewsByAuthor(Carbon $from)
    {
        return DB::table('article_view_daily')
            ->join('articles', 'articles.id', '=', 'article_view_daily.article_id')
            ->join('authors', 'authors.id', '=', 'articles.author_id')
            ->where('article_view_daily.viewed_on', '>=', $from->toDateString())
            ->groupBy('authors.id', 'authors.name', 'authors.slug')
            ->selectRaw('authors.name, authors.slug, SUM(article_view_daily.views) as views')
            ->orderByDesc('views')
            ->limit(6)
            ->get();
    }
}
