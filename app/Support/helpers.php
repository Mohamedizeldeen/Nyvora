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

if (! function_exists('newsletter_enabled')) {
    /**
     * Is the newsletter offered to readers?
     *
     * When off, the signup form disappears, the /subscribe route refuses new
     * signups, and the policy pages stop describing data we no longer collect.
     * Existing confirm/unsubscribe links keep working regardless, so anyone
     * already on the list can still leave.
     */
    function newsletter_enabled(): bool
    {
        return Setting::bool('newsletter_enabled');
    }
}

if (! function_exists('analytics_id')) {
    /**
     * The Google Analytics measurement ID, or null when analytics is off.
     */
    function analytics_id(): ?string
    {
        $id = trim((string) Setting::get('analytics_measurement_id'));

        return $id !== '' ? $id : null;
    }
}

if (! function_exists('tracking_needs_consent')) {
    /**
     * Does this page load anything a reader has to consent to?
     *
     * True when analytics or advertising is configured. Both set non-essential
     * cookies, which UK/EU rules say may only be set with permission.
     */
    function tracking_needs_consent(): bool
    {
        return analytics_id() !== null || filled(Setting::get('adsense_client_id'));
    }
}

if (! function_exists('comments_enabled')) {
    /**
     * Are reader comments offered at all?
     *
     * Independent of moderation: even with this on, every comment waits for an
     * administrator to approve it before anyone else can see it.
     */
    function comments_enabled(): bool
    {
        return Setting::bool('comments_enabled');
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
