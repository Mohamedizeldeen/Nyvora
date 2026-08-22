<?php

use App\Models\Article;
use App\Models\Setting;
use App\Models\User;

/**
 * AdSense is configured entirely from Admin → Settings. No file is edited to
 * turn ads on, and nothing Google-related loads until a publisher ID is saved.
 */

/**
 * A minimal valid Settings payload. The form posts every field at once, so a
 * test that changes one still has to send the required ones.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function settingsPayload(array $overrides = []): array
{
    return array_merge([
        'site_tagline' => 'Tagline',
        'footer_description' => 'Blurb.',
        'articles_per_page' => 8,
        'promo_tone' => 'accent',
    ], $overrides);
}

/*
|--------------------------------------------------------------------------
| Nothing loads until it is configured
|--------------------------------------------------------------------------
*/

it('loads no Google code at all while unconfigured', function () {
    article();

    $this->get('/')
        ->assertOk()
        ->assertDontSee('pagead2.googlesyndication.com', escape: false)
        ->assertDontSee('class="adsbygoogle"', escape: false);

    // An empty ads.txt declares that nobody may sell this inventory, which is
    // worse than not having one — so it 404s instead.
    $this->get('/ads.txt')->assertNotFound();
});

it('still reserves the ad space so the layout can be judged', function () {
    article();

    $this->get('/')
        ->assertOk()
        ->assertSee('id="ad-slot-1"', escape: false)
        ->assertSee('id="ad-slot-2"', escape: false)
        ->assertSee('height: 250px', escape: false);
});

/*
|--------------------------------------------------------------------------
| Once the publisher pastes their code
|--------------------------------------------------------------------------
*/

it('publishes ads.txt as soon as a publisher ID is saved', function () {
    Setting::put(['adsense_client_id' => 'ca-pub-1234567890123456']);

    $this->get('/ads.txt')
        ->assertOk()
        ->assertHeader('content-type', 'text/plain; charset=UTF-8')
        // Exactly the line Google looks for. Note `pub-`, not `ca-pub-`: an
        // ads.txt record names the bare publisher id. Getting this wrong yields
        // a file that fetches fine and authorises nobody, and AdSense then
        // reports the status as "Not found".
        ->assertSee('google.com, pub-1234567890123456, DIRECT, f08c47fec0942fa0');
});

it('never writes the ca- prefix into ads.txt', function () {
    Setting::put(['adsense_client_id' => 'ca-pub-1234567890123456']);

    $body = $this->get('/ads.txt')->assertOk()->getContent();

    expect($body)->not->toContain('ca-pub-');
});

it('serves ads.txt as one cacheable line with no cookies', function () {
    Setting::put(['adsense_client_id' => 'ca-pub-1234567890123456']);

    $response = $this->get('/ads.txt')->assertOk();

    // A crawler-only file. Session cookies would stop the CDN caching it.
    expect($response->headers->getCookies())->toBeEmpty()
        ->and($response->headers->get('Cache-Control'))->toContain('public')
        ->and(trim($response->getContent()))->not->toContain("\n");
});

it('accepts the pub- spelling AdSense shows on its account page', function () {
    // AdSense → Account → Settings displays "pub-1234567890123456"; the script
    // tag it hands you says "ca-pub-…". Both must be accepted.
    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->put('/admin/settings', settingsPayload(['adsense_client_id' => 'pub-1234567890123456']))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    Setting::flush();

    // Stored canonically for the ad tags…
    expect(Setting::get('adsense_client_id'))->toBe('ca-pub-1234567890123456');
    // …and emitted bare for ads.txt.
    $this->get('/ads.txt')->assertSee('google.com, pub-1234567890123456, DIRECT,');
});

it('accepts a whole pasted script tag as the publisher ID', function () {
    $tag = '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1234567890123456" crossorigin="anonymous"></script>';

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->put('/admin/settings', settingsPayload(['adsense_client_id' => $tag]))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    Setting::flush();

    expect(Setting::get('adsense_client_id'))->toBe('ca-pub-1234567890123456');
});

it('turns a placement live when its slot is configured', function () {
    // The in-feed placement only renders once the feed is more than four rows.
    Article::factory()->count(8)->create();
    Setting::put([
        'adsense_client_id' => 'ca-pub-1234567890123456',
        'adsense_slot_sidebar' => '5544332211',
    ]);

    $response = $this->get('/')->assertOk();

    $response
        ->assertSee('pagead2.googlesyndication.com', escape: false)
        ->assertSee('data-ad-client="ca-pub-1234567890123456"', escape: false)
        ->assertSee('data-ad-slot="5544332211"', escape: false);

    // The placement with no slot configured stays a placeholder.
    $response->assertSee('AD SLOT: ad-slot-3', escape: false);
});

it('accepts a pasted ad unit snippet and keeps only the slot id', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $snippet = '<ins class="adsbygoogle" style="display:block" '
        .'data-ad-client="ca-pub-1234567890123456" data-ad-slot="9876543210"></ins>'
        .'<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';

    $this->actingAs($admin)->put('/admin/settings', [
        'site_tagline' => 'Tagline',
        'footer_description' => 'Blurb.',
        'articles_per_page' => 8,
        'promo_tone' => 'accent',
        'adsense_client_id' => 'ca-pub-1234567890123456',
        'adsense_slot_sidebar' => $snippet,
        'adsense_slot_leaderboard' => '1112223334',
    ])->assertRedirect()->assertSessionHasNoErrors();

    Setting::flush();

    expect(setting('adsense_slot_sidebar'))->toBe('9876543210')
        ->and(setting('adsense_slot_leaderboard'))->toBe('1112223334');
});

it('rejects a publisher ID that is not a real one', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->put('/admin/settings', [
        'site_tagline' => 'Tagline',
        'footer_description' => 'Blurb.',
        'articles_per_page' => 8,
        'promo_tone' => 'accent',
        'adsense_client_id' => 'pub-nonsense',
    ])->assertSessionHasErrors('adsense_client_id');
});

it('adds auto ads only when asked', function () {
    article();
    Setting::put(['adsense_client_id' => 'ca-pub-1234567890123456']);

    $this->get('/')->assertOk()->assertDontSee('enable_page_level_ads', escape: false);

    Setting::put(['adsense_auto_ads' => '1']);

    $this->get('/')->assertOk()->assertSee('enable_page_level_ads', escape: false);
});
