<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * robots.txt, generated so the sitemap URL always matches the live domain.
     *
     * Staging and other non-production environments are closed to crawlers
     * outright — a duplicate copy of the site competing in search results is a
     * far more common SEO problem than a missing one.
     */
    public function index(): Response
    {
        $lines = ['User-agent: *'];

        if (app()->environment('production')) {
            $lines[] = 'Disallow: /admin';
            $lines[] = 'Disallow: /login';
            // Search result pages add no value to an index and dilute crawl budget.
            $lines[] = 'Disallow: /search';
            $lines[] = 'Disallow: /newsletter/';
            $lines[] = '';
            $lines[] = 'Sitemap: '.route('sitemap');
        } else {
            $lines[] = 'Disallow: /';
        }

        return response(implode("\n", $lines)."\n")
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
