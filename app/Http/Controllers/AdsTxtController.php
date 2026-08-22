<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Response;

class AdsTxtController extends Controller
{
    /**
     * ads.txt — the file Google checks to confirm who may sell this site's
     * inventory.
     *
     * AdSense reports "Ads.txt status: Not found" until this file exists *and*
     * contains a line naming its own publisher id, so it is generated from the
     * saved setting rather than being a static file somebody has to remember
     * to update.
     *
     * Returns 404 while no publisher id is configured: an empty ads.txt is
     * worse than none, because it declares that nobody is authorised to sell.
     */
    public function index(): Response
    {
        $publisherId = Setting::adsTxtPublisherId();

        abort_if($publisherId === '', 404);

        // The relationship is DIRECT, and f08c47fec0942fa0 is Google's own
        // certification authority id — the same for every AdSense publisher.
        $line = sprintf('google.com, %s, DIRECT, f08c47fec0942fa0', $publisherId);

        return response($line."\n")
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            // Crawler-only file that changes about never. Explicit public
            // caching lets the CDN answer Google directly; without it Laravel
            // sends "no-cache, private" and every check falls through to PHP.
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
