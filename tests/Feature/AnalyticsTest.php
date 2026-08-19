<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Google Analytics and consent
|--------------------------------------------------------------------------
*/

it('loads no analytics code while no measurement ID is set', function () {
    article();

    $this->get('/')
        ->assertOk()
        ->assertDontSee('googletagmanager.com', escape: false)
        // With nothing to consent to, the banner would be noise.
        ->assertDontSee('data-consent-banner', escape: false);
});

it('adds the tag with consent denied by default', function () {
    article();
    Setting::put(['analytics_measurement_id' => 'G-L86F9KBYKG']);

    $response = $this->get('/')->assertOk();

    $response
        ->assertSee('googletagmanager.com/gtag/js?id=G-L86F9KBYKG', escape: false)
        ->assertSee("gtag('config', 'G-L86F9KBYKG')", escape: false)
        // Consent Mode v2: nothing is stored until the reader accepts.
        ->assertSee("analytics_storage: 'denied'", escape: false)
        ->assertSee("ad_user_data: 'denied'", escape: false)
        ->assertSee("ad_personalization: 'denied'", escape: false)
        ->assertSee('data-consent-banner', escape: false);
});

it('rejects a measurement ID that is not a real one', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->put('/admin/settings', [
        'site_tagline' => 'Tagline',
        'footer_description' => 'Blurb.',
        'articles_per_page' => 8,
        'promo_tone' => 'accent',
        'analytics_measurement_id' => 'UA-12345',
    ])->assertSessionHasErrors('analytics_measurement_id');
});

it('keeps the cookie and privacy policies honest about analytics', function () {
    // Off: the policies may claim there is no analytics.
    $this->get('/cookie-policy')->assertOk()->assertSee('No Google Analytics');

    Setting::put(['analytics_measurement_id' => 'G-L86F9KBYKG']);

    // On: that claim must be gone, and the cookies must be listed and explained.
    $this->get('/cookie-policy')
        ->assertOk()
        ->assertDontSee('No Google Analytics')
        ->assertSee('_ga_L86F9KBYKG')
        ->assertSee('off until you accept them', escape: false);

    $this->get('/privacy-policy')
        ->assertOk()
        ->assertSee('Analytics')
        ->assertSee('no analytics cookie is set', escape: false)
        ->assertSee('Google Ireland Limited');
});

/*
|--------------------------------------------------------------------------
| Reports
|--------------------------------------------------------------------------
*/

it('records a daily bucket for every article read', function () {
    $article = article();

    $this->get("/article/{$article->slug}")->assertOk();
    $this->get("/article/{$article->slug}")->assertOk();

    $row = DB::table('article_view_daily')->where('article_id', $article->id)->first();

    expect($row->views)->toBe(2)
        ->and($row->viewed_on)->toBe(now()->toDateString())
        // The running total stays in step.
        ->and($article->fresh()->views_count)->toBe($article->views_count + 2);
});

it('keeps the reports page behind the admin gate', function () {
    $this->get('/admin/reports')->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get('/admin/reports')->assertForbidden();
});

it('reports views over the chosen period', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $article = article(['title' => 'A widely read story']);

    DB::table('article_view_daily')->insert([
        ['article_id' => $article->id, 'viewed_on' => now()->toDateString(), 'views' => 120],
        ['article_id' => $article->id, 'viewed_on' => now()->subDays(3)->toDateString(), 'views' => 80],
        // Outside a 7-day window, inside a 30-day one.
        ['article_id' => $article->id, 'viewed_on' => now()->subDays(20)->toDateString(), 'views' => 500],
    ]);

    $this->actingAs($admin)->get('/admin/reports?days=7')
        ->assertOk()
        ->assertSee('A widely read story')
        ->assertSee('200');

    $this->actingAs($admin)->get('/admin/reports?days=30')
        ->assertOk()
        ->assertSee('700');
});

it('falls back to a sane range when given a silly one', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->get('/admin/reports?days=99999')
        ->assertOk()
        ->assertSee('Last 30 days');
});

it('survives having no data at all', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->get('/admin/reports')
        ->assertOk()
        ->assertSee('No views recorded yet');
});

it('drops daily rows when the article goes', function () {
    $article = article();
    $this->get("/article/{$article->slug}");

    expect(DB::table('article_view_daily')->count())->toBe(1);

    $article->delete();

    expect(DB::table('article_view_daily')->count())->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Consent manager
|--------------------------------------------------------------------------
*/

it('shows our own banner and denies everywhere by default', function () {
    article();
    Setting::put(['analytics_measurement_id' => 'G-L86F9KBYKG', 'consent_manager' => 'built_in']);

    $this->get('/')
        ->assertOk()
        ->assertSee('data-consent-banner', escape: false)
        ->assertSee("analytics_storage: 'denied'", escape: false)
        // No region scoping: our banner asks everyone, everywhere.
        ->assertDontSee('region:', escape: false);
});

it('stands aside when Google\'s certified CMP is in charge', function () {
    article();
    Setting::put(['analytics_measurement_id' => 'G-L86F9KBYKG', 'consent_manager' => 'google']);

    $response = $this->get('/')->assertOk();

    // Two banners would send conflicting consent signals.
    $response->assertDontSee('data-consent-banner', escape: false);

    // Denied where consent is required...
    $response->assertSee('region:', escape: false)
        ->assertSee('"GB"', escape: false)
        ->assertSee('"DE"', escape: false)
        ->assertSee('"CH"', escape: false);

    // ...and granted elsewhere, or Google's message never appearing outside the
    // EEA would leave those readers permanently denied and unmeasurable.
    $response->assertSee("analytics_storage: 'granted'", escape: false);
});

it('only accepts a consent manager it knows about', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->put('/admin/settings', [
        'site_tagline' => 'Tagline',
        'footer_description' => 'Blurb.',
        'articles_per_page' => 8,
        'promo_tone' => 'accent',
        'consent_manager' => 'some-other-cmp',
    ])->assertSessionHasErrors('consent_manager');
});
