<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * robots.txt, generated so the sitemap URL always matches the live domain.
     *
     * Whether crawlers are welcome is an explicit decision — see config/seo.php
     * and Admin → Settings — rather than something inferred from APP_ENV, so a
     * deploy cannot accidentally hide the whole site from search.
     */
    public function index(): Response
    {
        $lines = ['User-agent: *'];

        if (site_is_indexable()) {
            // Crawl the journalism; skip everything with no search value.
            $lines[] = 'Disallow: /admin';
            $lines[] = 'Disallow: /login';
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
