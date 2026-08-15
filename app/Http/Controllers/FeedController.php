<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    /**
     * RSS 2.0 feed of the latest stories.
     *
     * News aggregators, readers and Google Discover all consume this, and it
     * is the cheapest distribution a publication can offer.
     */
    public function index(): Response
    {
        $articles = Article::query()
            ->published()
            ->with(['category', 'author'])
            ->latestFirst()
            ->take(30)
            ->get();

        return response()
            ->view('feed', ['articles' => $articles])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
