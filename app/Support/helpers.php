<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Read a site setting managed from Admin → Settings.
     *
     * Backed by a single cached array, so calling this repeatedly inside a
     * template costs nothing after the first read.
     */
    function setting(string $key, ?string $fallback = null): ?string
    {
        return Setting::get($key, $fallback);
    }
}

if (! function_exists('setting_bool')) {
    /**
     * Read a checkbox-style setting as a boolean.
     */
    function setting_bool(string $key): bool
    {
        return Setting::bool($key);
    }
}

if (! function_exists('site_is_indexable')) {
    /**
     * May search engines index this site?
     *
     * Both switches have to agree: SITE_INDEXABLE guards the deployment
     * (staging sets it to false), and the admin toggle guards editorially.
     * Either one can block; neither can force indexing on alone.
     */
    function site_is_indexable(): bool
    {
        return (bool) config('seo.indexable') && Setting::bool('search_indexable');
    }
}
