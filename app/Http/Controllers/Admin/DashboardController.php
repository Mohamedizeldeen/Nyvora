<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Subscriber;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Newsroom overview: headline numbers, the newest stories and the
     * best-read ones.
     */
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'published' => Article::query()->published()->count(),
                'drafts' => Article::query()->whereNull('published_at')->count(),
                'scheduled' => Article::query()->whereNotNull('published_at')->where('published_at', '>', now())->count(),
                'views' => (int) Article::query()->sum('views_count'),
                'categories' => Category::query()->count(),
                'authors' => Author::query()->count(),
                'subscribers' => Subscriber::query()->count(),
                'subscribers_week' => Subscriber::query()->where('created_at', '>=', now()->subWeek())->count(),
            ],
            'recent' => Article::query()
                ->with(['category', 'author'])
                ->latest('created_at')
                ->take(6)
                ->get(),
            'topPerforming' => Article::query()
                ->published()
                ->with('category')
                ->popular()
                ->take(6)
                ->get(),
        ]);
    }
}
