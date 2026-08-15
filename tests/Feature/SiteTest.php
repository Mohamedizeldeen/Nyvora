<?php

use App\Mail\ConfirmSubscription;
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Subscriber;
use Database\Seeders\NewsSeeder;
use Illuminate\Support\Facades\Mail;

/**
 * Build a published article with a real category and author attached.
 */
function article(array $attributes = []): Article
{
    return Article::factory()->create($attributes);
}

/*
|--------------------------------------------------------------------------
| Homepage
|--------------------------------------------------------------------------
*/

it('renders the homepage with the hero, feed and sidebar', function () {
    $category = Category::factory()->create(['name' => 'Security', 'slug' => 'security', 'color' => '#DC2626']);
    $author = Author::factory()->create(['name' => 'Priya Raman']);

    $featured = article([
        'title' => 'Passkeys go mainstream',
        'category_id' => $category->id,
        'author_id' => $author->id,
        'is_featured' => true,
        'views_count' => 9000,
    ]);

    // The headlines widget lists stories that are not already in the hero, and
    // the in-feed ad only appears once the feed is more than four rows deep.
    Article::factory()->count(6)->create(['category_id' => $category->id, 'author_id' => $author->id]);

    $this->get('/')
        ->assertOk()
        ->assertSee($featured->title)
        ->assertSee('Top Headlines')
        ->assertSee('Latest News')
        ->assertSee('Most Popular')
        ->assertSee('Priya Raman')
        // The category colour from the database drives the label tint.
        ->assertSee('#DC2626', escape: false)
        // All three AdSense placeholders are present and marked up for later.
        ->assertSee('id="ad-slot-1"', escape: false)
        ->assertSee('id="ad-slot-2"', escape: false)
        ->assertSee('id="ad-slot-3"', escape: false)
        ->assertSee('ADSENSE SLOT', escape: false);
});

it('renders the homepage even with no articles at all', function () {
    $this->get('/')->assertOk()->assertSee('Latest News');
});

it('paginates the latest news feed', function () {
    article(['is_featured' => true]);
    Article::factory()->count(20)->create();

    $this->get('/')->assertOk()->assertSee('page=2', escape: false);
    $this->get('/?page=2')->assertOk();
});

it('hides drafts and scheduled posts from the homepage', function () {
    $draft = Article::factory()->draft()->create(['title' => 'Unfinished draft story']);
    $scheduled = Article::factory()->scheduled()->create(['title' => 'Embargoed until later']);
    $live = article(['title' => 'A published story']);

    $this->get('/')
        ->assertOk()
        ->assertSee($live->title)
        ->assertDontSee($draft->title)
        ->assertDontSee($scheduled->title);
});

/*
|--------------------------------------------------------------------------
| Category archive
|--------------------------------------------------------------------------
*/

it('shows a category archive by slug and excludes other categories', function () {
    $ai = Category::factory()->create(['name' => 'AI', 'slug' => 'ai']);
    $space = Category::factory()->create(['name' => 'Space', 'slug' => 'space']);

    $inSection = article(['title' => 'A story about models', 'category_id' => $ai->id]);
    $elsewhere = article(['title' => 'A story about rockets', 'category_id' => $space->id]);

    $this->get('/category/ai')
        ->assertOk()
        ->assertSee($inSection->title)
        ->assertDontSee($elsewhere->title);
});

it('404s on an unknown category slug', function () {
    $this->get('/category/does-not-exist')->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Single article
|--------------------------------------------------------------------------
*/

it('shows a single article and its related stories', function () {
    $category = Category::factory()->create(['slug' => 'gadgets', 'name' => 'Gadgets']);
    $article = article(['title' => 'Hands on with the Aurora Fold', 'category_id' => $category->id]);
    $related = article(['title' => 'The best travel chargers', 'category_id' => $category->id]);

    $this->get("/article/{$article->slug}")
        ->assertOk()
        ->assertSee($article->title)
        ->assertSee($related->title)
        ->assertSee('Written by')
        ->assertSee('NewsArticle', escape: false);
});

it('offers social sharing on every story', function () {
    $article = article(['title' => 'Fusion & robots: a story']);
    $url = rawurlencode(route('article.show', $article));

    $response = $this->get("/article/{$article->slug}")->assertOk();

    // Each network gets a plain link carrying the canonical article URL.
    $response
        ->assertSee('twitter.com/intent/tweet?url='.$url, escape: false)
        ->assertSee('facebook.com/sharer/sharer.php?u='.$url, escape: false)
        ->assertSee('linkedin.com/sharing/share-offsite/?url='.$url, escape: false)
        ->assertSee('api.whatsapp.com/send?text=', escape: false)
        ->assertSee('t.me/share/url?url='.$url, escape: false)
        ->assertSee('data-share-copy', escape: false)
        ->assertSee('data-share-native', escape: false);
});

it('exposes the metadata social platforms read when a link is shared', function () {
    $category = Category::factory()->create(['name' => 'Space', 'slug' => 'space']);
    $author = Author::factory()->create(['name' => 'Amara Bello']);
    $article = article([
        'title' => 'A lander touches down',
        'excerpt' => 'Six days of surface operations.',
        'category_id' => $category->id,
        'author_id' => $author->id,
        'thumbnail_url' => 'https://example.com/lander.jpg',
    ]);

    $this->get("/article/{$article->slug}")
        ->assertOk()
        ->assertSee('<meta property="og:type" content="article">', escape: false)
        ->assertSee('<meta property="og:image" content="https://example.com/lander.jpg">', escape: false)
        ->assertSee('<meta property="article:author" content="Amara Bello">', escape: false)
        ->assertSee('<meta property="article:section" content="Space">', escape: false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', escape: false);
});

it('increments the view count on each read', function () {
    $article = article(['views_count' => 10]);

    $this->get("/article/{$article->slug}")->assertOk();
    $this->get("/article/{$article->slug}")->assertOk();

    expect($article->fresh()->views_count)->toBe(12);
});

it('404s on drafts, scheduled posts and unknown slugs', function () {
    $draft = Article::factory()->draft()->create();
    $scheduled = Article::factory()->scheduled()->create();

    $this->get("/article/{$draft->slug}")->assertNotFound();
    $this->get("/article/{$scheduled->slug}")->assertNotFound();
    $this->get('/article/no-such-story')->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Static pages
|--------------------------------------------------------------------------
*/

it('serves the pages AdSense review expects', function (string $path, string $expected) {
    $this->get($path)->assertOk()->assertSee($expected);
})->with([
    ['/about', 'About us'],
    ['/contact', 'Contact'],
    ['/privacy-policy', 'Privacy policy'],
]);

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

// The sidebar lists popular stories site-wide on every page, so these assert
// on the result count rather than the absence of a headline anywhere on the page.
it('finds articles by keyword and ignores the rest', function () {
    $match = article(['title' => 'Fusion startups moved their timelines up']);
    article(['title' => 'Repairability scores are changing phones']);

    $this->get('/search?q=fusion')
        ->assertOk()
        ->assertSee('1 result for')
        ->assertSee($match->title);
});

it('matches on the excerpt as well as the headline', function () {
    article(['title' => 'An unrelated headline', 'excerpt' => 'A story about tokamak reactors.']);

    $this->get('/search?q=tokamak')->assertOk()->assertSee('1 result for');
});

it('treats LIKE wildcards in a search term as literal characters', function () {
    article(['title' => 'A perfectly ordinary headline']);
    article(['title' => 'Another ordinary headline']);

    // "%" must search for a literal percent sign, not match every row.
    $this->get('/search?q=%25')->assertOk()->assertSee('0 results for');

    // "_" is the single-character wildcard in SQL; it must not match "A".
    $this->get('/search?q=_')->assertOk()->assertSee('0 results for');
});

/*
|--------------------------------------------------------------------------
| Newsletter
|--------------------------------------------------------------------------
*/

it('stores a signup as pending and emails a confirmation link', function () {
    Mail::fake();

    $this->post('/subscribe', ['email' => '  READER@Example.COM  '])->assertRedirect();

    $subscriber = Subscriber::query()->firstOrFail();

    expect($subscriber->email)->toBe('reader@example.com')
        ->and($subscriber->status())->toBe('pending')
        ->and($subscriber->token)->not->toBeEmpty();

    Mail::assertQueued(ConfirmSubscription::class, fn ($mail) => $mail->hasTo('reader@example.com'));
});

it('confirms the subscription when the emailed link is opened', function () {
    Mail::fake();
    $this->post('/subscribe', ['email' => 'reader@example.com']);

    $subscriber = Subscriber::query()->firstOrFail();

    $this->get(route('newsletter.confirm', $subscriber))
        ->assertOk()
        ->assertSee('You are on the list');

    expect($subscriber->fresh()->status())->toBe('confirmed');
});

it('404s on an unknown or tampered confirmation token', function () {
    $this->get('/newsletter/confirm/not-a-real-token')->assertNotFound();
});

it('unsubscribes from the emailed link, by GET or one-click POST', function () {
    Mail::fake();
    $this->post('/subscribe', ['email' => 'reader@example.com']);
    $subscriber = Subscriber::query()->firstOrFail();
    $subscriber->confirm();

    $this->get(route('newsletter.unsubscribe', $subscriber))
        ->assertOk()
        ->assertSee('You have been unsubscribed');

    expect($subscriber->fresh()->status())->toBe('unsubscribed');

    // RFC 8058 one-click: a POST with no CSRF token must still work.
    $other = Subscriber::query()->create([
        'email' => 'other@example.com',
        'token' => Subscriber::newToken(),
        'confirmed_at' => now(),
    ]);

    $this->post(route('newsletter.unsubscribe', $other))->assertOk();

    expect($other->fresh()->status())->toBe('unsubscribed');
});

it('does not send a second confirmation to an already confirmed address', function () {
    Mail::fake();

    $this->post('/subscribe', ['email' => 'reader@example.com']);
    Subscriber::query()->firstOrFail()->confirm();

    $this->post('/subscribe', ['email' => 'reader@example.com'])->assertSessionHasNoErrors();

    expect(Subscriber::query()->count())->toBe(1);
    Mail::assertQueuedCount(1);
});

it('restarts the opt-in flow for someone who previously unsubscribed', function () {
    Mail::fake();

    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'token' => Subscriber::newToken(),
        'confirmed_at' => now()->subMonth(),
        'unsubscribed_at' => now()->subDay(),
    ]);
    $oldToken = $subscriber->token;

    $this->post('/subscribe', ['email' => 'reader@example.com'])->assertRedirect();

    $subscriber->refresh();

    expect($subscriber->status())->toBe('pending')
        // A fresh token invalidates the link from the previous subscription.
        ->and($subscriber->token)->not->toBe($oldToken);

    Mail::assertQueued(ConfirmSubscription::class);
});

it('rejects an invalid email address', function () {
    Mail::fake();

    $this->post('/subscribe', ['email' => 'not-an-email'])
        ->assertSessionHasErrors('email');

    expect(Subscriber::query()->count())->toBe(0);
    Mail::assertNothingQueued();
});

/*
|--------------------------------------------------------------------------
| SEO endpoints
|--------------------------------------------------------------------------
*/

it('serves a sitemap listing published stories and sections', function () {
    $category = Category::factory()->create(['slug' => 'ai']);
    $live = article(['slug' => 'a-live-story', 'category_id' => $category->id]);
    $draft = Article::factory()->draft()->create(['slug' => 'a-draft-story']);

    $response = $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('content-type', 'application/xml; charset=UTF-8');

    $response
        ->assertSee(route('article.show', $live), escape: false)
        ->assertSee(route('category.show', $category), escape: false)
        ->assertSee(route('about'), escape: false)
        // Drafts, the admin and search must never be advertised to crawlers.
        ->assertDontSee('a-draft-story', escape: false)
        ->assertDontSee(route('search'), escape: false)
        ->assertDontSee(route('admin.dashboard'), escape: false);

    expect(simplexml_load_string($response->getContent()))->not->toBeFalse();
});

it('serves a valid RSS feed of the latest stories', function () {
    $article = article(['title' => 'A story worth syndicating']);

    $response = $this->get('/feed')
        ->assertOk()
        ->assertHeader('content-type', 'application/rss+xml; charset=UTF-8')
        ->assertSee('A story worth syndicating');

    $xml = simplexml_load_string($response->getContent());

    expect($xml)->not->toBeFalse()
        ->and((string) $xml->channel->item[0]->link)->toBe(route('article.show', $article));
});

it('blocks crawlers outside production and points them at the sitemap in it', function () {
    // Local/staging: a duplicate of the site must not compete in search results.
    $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /');

    app()->detectEnvironment(fn () => 'production');

    $this->get('/robots.txt')
        ->assertOk()
        ->assertSee('Disallow: /admin')
        ->assertSee('Sitemap: ', escape: false);
});

it('gives every page a self-referencing canonical, including page 2', function () {
    Article::factory()->count(20)->create();

    $this->get('/')->assertOk()->assertSee('<link rel="canonical" href="'.route('home').'">', escape: false);

    $this->get('/?page=2')
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.route('home').'?page=2">', escape: false)
        // ...and prev/next hints so crawlers can walk the archive.
        ->assertSee('<link rel="prev"', escape: false);
});

it('publishes organisation and breadcrumb structured data', function () {
    $category = Category::factory()->create(['name' => 'Security', 'slug' => 'security']);
    $article = article(['category_id' => $category->id]);

    $this->get('/')->assertOk()->assertSee('NewsMediaOrganization', escape: false);

    $this->get("/article/{$article->slug}")
        ->assertOk()
        ->assertSee('BreadcrumbList', escape: false)
        ->assertSee('NewsArticle', escape: false);

    $this->get('/category/security')->assertOk()->assertSee('BreadcrumbList', escape: false);
});

it('renders a branded 404 page that is not indexed', function () {
    Category::factory()->create(['name' => 'Gadgets', 'slug' => 'gadgets']);

    $this->get('/article/no-such-story')
        ->assertNotFound()
        ->assertSee('We cannot find that page')
        ->assertSee('noindex, follow', escape: false)
        ->assertSee('Gadgets');
});

/*
|--------------------------------------------------------------------------
| Seeder
|--------------------------------------------------------------------------
*/

it('seeds a browsable newsroom', function () {
    $this->seed(NewsSeeder::class);

    expect(Category::query()->count())->toBe(5)
        ->and(Author::query()->count())->toBe(6)
        ->and(Article::query()->published()->count())->toBe(20)
        // The draft and the scheduled post exist but are not public.
        ->and(Article::query()->count())->toBe(22);

    $this->get('/')->assertOk();
});
