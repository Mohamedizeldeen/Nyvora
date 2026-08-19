<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * The settings cache must never be able to hide a newly added setting.
 *
 * The first version cached the defaults merged with the stored rows, forever.
 * That meant any setting introduced in a later deploy was missing from the
 * cached array, and every view reading $settings['new_key'] threw
 * "Undefined array key" — a 500 on the admin settings page in production,
 * fixable only by clearing the cache by hand on each server.
 */
it('exposes a newly added setting even with a stale cache', function () {
    // Exactly what a server that deployed earlier has sitting in its cache.
    Cache::forever('settings.all', ['site_tagline' => 'Cached under the old shape']);
    Cache::forever('settings.stored.v2', ['site_tagline' => 'Saved tagline']);

    $settings = Setting::all_settings();

    // Every key the code knows about must be present, whatever the cache holds.
    expect(array_keys($settings))->toContain(...array_keys(Setting::DEFAULTS));

    // A value that really was saved still beats the default.
    expect($settings['site_tagline'])->toBe('Saved tagline');
});

it('renders the admin settings page against a stale cache', function () {
    Cache::forever('settings.stored.v2', ['site_tagline' => 'Only this one was ever saved']);

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get('/admin/settings')
        ->assertOk()
        ->assertSee('Only this one was ever saved')
        // The sections whose keys were added after that cache entry.
        ->assertSee('Reader comments')
        ->assertSee('Google AdSense')
        ->assertSee('Measurement ID');
});

it('still returns defaults when the settings table is unreadable', function () {
    Cache::flush();
    Schema::drop('settings');

    // A fresh checkout before migrating must not take the whole site down.
    expect(Setting::get('site_tagline'))->toBe(Setting::DEFAULTS['site_tagline']);
});
