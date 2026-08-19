<?php

use App\Models\ContactMessage;
use App\Models\User;

/**
 * The site publishes no email addresses. Every former mailto: link is now a
 * form that opens in a popup and stores the message for the newsroom to read.
 */

/*
|--------------------------------------------------------------------------
| Sending
|--------------------------------------------------------------------------
*/

it('stores a message and thanks the sender', function () {
    $this->post('/contact', [
        'topic' => 'tip',
        'name' => 'Sam Reader',
        'email' => '  SAM@Example.COM ',
        'body' => 'I have information about a build tool that shipped a backdoor.',
    ])->assertRedirect()->assertSessionHas('contact_sent');

    $message = ContactMessage::query()->firstOrFail();

    expect($message->topic)->toBe('tip')
        ->and($message->name)->toBe('Sam Reader')
        ->and($message->email)->toBe('sam@example.com')
        ->and($message->isUnread())->toBeTrue();
});

it('answers the popup with JSON so it can show the thank-you without reloading', function () {
    $response = $this->postJson('/contact', [
        'topic' => 'security',
        'name' => 'Alex Finder',
        'email' => 'alex@example.com',
        'body' => 'There is an XSS in the search parameter handling on your site.',
    ])->assertOk();

    expect($response->json('ok'))->toBeTrue()
        ->and($response->json('message'))->toContain('Thank you')
        ->and($response->json('topic'))->toBe('Security report');
});

it('rejects incomplete or malformed messages', function () {
    $this->postJson('/contact', [
        'topic' => 'not-a-real-topic',
        'name' => 'A',
        'email' => 'not-an-email',
        'body' => 'short',
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['topic', 'name', 'email', 'body']);

    expect(ContactMessage::query()->count())->toBe(0);
});

it('silently refuses anything that fills the honeypot', function () {
    $this->post('/contact', [
        'topic' => 'general',
        'name' => 'Spam Bot',
        'email' => 'bot@example.com',
        'body' => 'Buy cheap things right now from my website.',
        'website' => 'http://spam.example',
    ])->assertSessionHasErrors('website');

    expect(ContactMessage::query()->count())->toBe(0);
});

it('throttles a flood from one connection', function () {
    $send = fn () => $this->postJson('/contact', [
        'topic' => 'general',
        'name' => 'Flood Test',
        'email' => 'flood@example.com',
        'body' => 'This is a repeated message used to exercise the rate limiter.',
    ]);

    for ($i = 0; $i < 5; $i++) {
        $send()->assertOk();
    }

    $send()->assertStatus(429);
});

/*
|--------------------------------------------------------------------------
| The forms that replaced the addresses
|--------------------------------------------------------------------------
*/

it('publishes no email address of its own anywhere on the site', function () {
    $paths = ['/', '/contact', '/privacy-policy', '/cookie-policy', '/terms',
        '/editorial-policy', '/advertise', '/team', '/about', '/rss'];

    foreach ($paths as $path) {
        $body = $this->get($path)->assertOk()->getContent();

        // The share row's "mailto:?subject=" opens the reader's own client to
        // share an article — that is not one of our addresses, so it stays.
        expect($body)->not->toMatch('/mailto:[^"?]/', "{$path} still publishes an email address");
    }
});

it('offers a topic-specific form everywhere an address used to be', function (string $path, string $topic) {
    $this->get($path)
        ->assertOk()
        ->assertSee('data-contact-open', escape: false)
        ->assertSee('data-contact-topic="'.$topic.'"', escape: false);
})->with([
    ['/privacy-policy', 'privacy'],
    ['/cookie-policy', 'privacy'],
    ['/terms', 'security'],
    ['/editorial-policy', 'correction'],
    ['/advertise', 'advertising'],
    ['/team', 'pitch'],
    ['/contact', 'tip'],
]);

it('renders the popup once per page, and an inline form for people without JS', function () {
    $home = $this->get('/')->assertOk()->getContent();
    expect(substr_count($home, 'data-contact-dialog'))->toBe(1);

    // The contact page carries the same form inline, which is where the popup
    // triggers point when JavaScript is unavailable.
    $this->get('/contact')
        ->assertOk()
        ->assertSee('id="contact-form"', escape: false)
        ->assertSee('action="'.route('contact.send').'"', escape: false);
});

/*
|--------------------------------------------------------------------------
| The newsroom inbox
|--------------------------------------------------------------------------
*/

it('keeps the inbox behind the admin gate', function () {
    $message = ContactMessage::query()->create([
        'topic' => 'tip', 'name' => 'Sam', 'email' => 'sam@example.com', 'body' => 'A tip about something.',
    ]);

    $this->get('/admin/messages')->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get('/admin/messages')->assertForbidden();
    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get('/admin/messages')->assertOk()->assertSee('Sam');
});

it('marks a message read when it is opened, and can delete it', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $message = ContactMessage::query()->create([
        'topic' => 'correction', 'name' => 'Dana', 'email' => 'dana@example.com',
        'body' => 'The funding figure in that story is wrong.',
    ]);

    expect($message->isUnread())->toBeTrue();

    $this->actingAs($admin)->get("/admin/messages/{$message->id}")
        ->assertOk()
        ->assertSee('The funding figure in that story is wrong.');

    expect($message->fresh()->isUnread())->toBeFalse();

    $this->actingAs($admin)->delete("/admin/messages/{$message->id}")->assertRedirect();
    expect(ContactMessage::query()->count())->toBe(0);
});

it('filters the inbox by topic and unread state', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    ContactMessage::query()->create(['topic' => 'tip', 'name' => 'Tipster', 'email' => 't@example.com', 'body' => 'A story tip for you.']);
    ContactMessage::query()->create(['topic' => 'advertising', 'name' => 'Buyer', 'email' => 'b@example.com', 'body' => 'We would like a rate card.', 'read_at' => now()]);

    $this->actingAs($admin)->get('/admin/messages?topic=tip')
        ->assertOk()->assertSee('Tipster')->assertDontSee('Buyer');

    $this->actingAs($admin)->get('/admin/messages?unread=1')
        ->assertOk()->assertSee('Tipster')->assertDontSee('Buyer');
});

it('describes the messages it stores in the privacy policy', function () {
    $this->get('/privacy-policy')
        ->assertOk()
        ->assertSee('Messages you send us')
        ->assertSee('we store the topic you picked')
        ->assertSee('no IP address is saved with');
});
