<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    /**
     * RSS 2.0 feed of the latest stories.
     *
     * News aggregators, readers and Google Discover all consume this, and it
     * is the cheapest distribution a publication can offer.
     *
     * A browser asking for this URL is sent to the human-readable /rss page
     * instead — raw XML is not something to show a reader. Anything that does
     * not explicitly prefer HTML (feed readers, curl, validators) still gets
     * the feed itself, so machine consumers are unaffected.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        if ($this->prefersHtml($request)) {
            return redirect()
                ->route('rss')
                // Without Vary, a shared cache could serve the redirect to a
                // feed reader, or the XML to a browser.
                ->header('Vary', 'Accept');
        }

        $articles = Article::query()
            ->published()
            ->with(['category', 'author'])
            ->latestFirst()
            ->take(30)
            ->get();

        return response()
            ->view('feed', ['articles' => $articles])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->header('Vary', 'Accept');
    }

    /**
     * The human-facing page: what the feed is and how to subscribe to it.
     */
    public function page(): View
    {
        return view('rss', [
            'articles' => Article::query()
                ->published()
                ->with(['category', 'author'])
                ->latestFirst()
                ->take(10)
                ->get(),
        ]);
    }

    /**
     * Is this a browser rather than a feed reader?
     *
     * Deliberately conservative: anything that names a feed type in Accept is
     * treated as a machine, and a missing or wildcard Accept (curl, most
     * validators) also gets the XML. Only a client that explicitly asks for
     * HTML and says nothing about feeds is redirected.
     */
    private function prefersHtml(Request $request): bool
    {
        $accept = mb_strtolower((string) $request->header('Accept', ''));

        if ($accept === '') {
            return false;
        }

        // Note: application/xml is NOT in this list — browsers send it with a
        // q-value in every request, so excluding it would never redirect anyone.
        foreach (['application/rss+xml', 'application/atom+xml', 'application/feed+json'] as $feedType) {
            if (str_contains($accept, $feedType)) {
                return false;
            }
        }

        return str_contains($accept, 'text/html');
    }
}
