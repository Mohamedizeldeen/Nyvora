<?php

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * A signed-in newsroom administrator.
 */
function admin(): User
{
    return User::factory()->create(['is_admin' => true]);
}

/*
|--------------------------------------------------------------------------
| Access control
|--------------------------------------------------------------------------
*/

it('sends guests to the login page', function (string $path) {
    $this->get($path)->assertRedirect(route('login'));
})->with(['/admin', '/admin/articles', '/admin/categories', '/admin/authors', '/admin/subscribers', '/admin/settings']);

it('refuses signed-in readers who are not administrators', function () {
    $reader = User::factory()->create(['is_admin' => false]);

    $this->actingAs($reader)->get('/admin')->assertForbidden();
    $this->actingAs($reader)->get('/admin/articles')->assertForbidden();
});

it('lets an administrator in', function () {
    $this->actingAs(admin())->get('/admin')->assertOk()->assertSee('Dashboard');
});

it('signs an administrator in through the login form', function () {
    $user = User::factory()->create(['is_admin' => true, 'password' => 'secret-password']);

    $this->post('/login', ['email' => $user->email, 'password' => 'secret-password'])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rejects a wrong password', function () {
    $user = User::factory()->create(['is_admin' => true, 'password' => 'secret-password']);

    $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('signs out', function () {
    $this->actingAs(admin())->post('/logout')->assertRedirect(route('home'));

    $this->assertGuest();
});

/*
|--------------------------------------------------------------------------
| Articles
|--------------------------------------------------------------------------
*/

it('creates a story, deriving the slug from the headline', function () {
    $category = Category::factory()->create();
    $author = Author::factory()->create();

    $this->actingAs(admin())->post('/admin/articles', [
        'title' => 'Helion Labs ships a smaller model',
        'slug' => '',
        'excerpt' => 'A short standfirst.',
        'body' => "First paragraph.\n\n## A subheading\n\nSecond paragraph.",
        'category_id' => $category->id,
        'author_id' => $author->id,
        'published_at' => now()->toDateTimeString(),
        'is_featured' => '1',
    ])->assertRedirect();

    $article = Article::query()->firstOrFail();

    expect($article->slug)->toBe('helion-labs-ships-a-smaller-model')
        ->and($article->is_featured)->toBeTrue()
        ->and($article->isPublished())->toBeTrue();

    // And it is immediately live on the public site.
    $this->get("/article/{$article->slug}")->assertOk()->assertSee('A subheading');
});

it('rejects a duplicate slug', function () {
    $existing = Article::factory()->create(['slug' => 'taken-slug']);

    $this->actingAs(admin())->post('/admin/articles', [
        'title' => 'Another story',
        'slug' => 'taken-slug',
        'body' => 'Body copy.',
        'category_id' => $existing->category_id,
        'author_id' => $existing->author_id,
    ])->assertSessionHasErrors('slug');

    expect(Article::query()->count())->toBe(1);
});

it('updates a story', function () {
    $article = Article::factory()->create(['title' => 'Old headline']);

    $this->actingAs(admin())->put("/admin/articles/{$article->slug}", [
        'title' => 'New headline',
        'slug' => $article->slug,
        'body' => 'Updated body.',
        'category_id' => $article->category_id,
        'author_id' => $article->author_id,
        'published_at' => now()->toDateTimeString(),
    ])->assertRedirect();

    expect($article->fresh()->title)->toBe('New headline');
});

it('deletes a story', function () {
    $article = Article::factory()->create();

    $this->actingAs(admin())->delete("/admin/articles/{$article->slug}")->assertRedirect();

    expect(Article::query()->count())->toBe(0);
});

it('toggles featured and published from the list', function () {
    $article = Article::factory()->create(['is_featured' => false]);

    $this->actingAs(admin())->post("/admin/articles/{$article->slug}/feature");
    expect($article->fresh()->is_featured)->toBeTrue();

    $this->actingAs(admin())->post("/admin/articles/{$article->slug}/publish");
    expect($article->fresh()->isPublished())->toBeFalse();

    $this->actingAs(admin())->post("/admin/articles/{$article->slug}/publish");
    expect($article->fresh()->isPublished())->toBeTrue();
});

it('filters the story list by status', function () {
    Article::factory()->create(['title' => 'A live story']);
    Article::factory()->draft()->create(['title' => 'A drafted story']);

    $this->actingAs(admin())->get('/admin/articles?status=draft')
        ->assertOk()
        ->assertSee('A drafted story')
        ->assertDontSee('A live story');
});

it('stores an uploaded thumbnail on the public disk', function () {
    Storage::fake('public');

    $category = Category::factory()->create();
    $author = Author::factory()->create();

    $this->actingAs(admin())->post('/admin/articles', [
        'title' => 'A story with a picture',
        'body' => 'Body copy.',
        'category_id' => $category->id,
        'author_id' => $author->id,
        'thumbnail' => UploadedFile::fake()->image('cover.jpg', 1200, 800),
    ])->assertRedirect();

    $article = Article::query()->firstOrFail();

    expect($article->thumbnail_url)->toStartWith('/storage/articles/');

    Storage::disk('public')->assertExists(str_replace('/storage/', '', $article->thumbnail_url));
});

/*
|--------------------------------------------------------------------------
| Categories and authors
|--------------------------------------------------------------------------
*/

it('creates a section with a validated colour', function () {
    $this->actingAs(admin())->post('/admin/categories', [
        'name' => 'Robotics',
        'slug' => '',
        'color' => '#112233',
    ])->assertRedirect();

    $category = Category::query()->firstOrFail();

    expect($category->slug)->toBe('robotics')
        ->and($category->color)->toBe('#112233');
});

it('rejects a colour that is not a plain hex', function () {
    $this->actingAs(admin())->post('/admin/categories', [
        'name' => 'Bad',
        'color' => 'red; background:url(javascript:alert(1))',
    ])->assertSessionHasErrors('color');

    expect(Category::query()->count())->toBe(0);
});

it('refuses to delete a section that still has stories', function () {
    $article = Article::factory()->create();

    $this->actingAs(admin())
        ->delete("/admin/categories/{$article->category->slug}")
        ->assertSessionHas('error');

    expect(Category::query()->count())->toBe(1);
});

it('deletes an empty section', function () {
    $category = Category::factory()->create();

    $this->actingAs(admin())->delete("/admin/categories/{$category->slug}")->assertRedirect();

    expect(Category::query()->count())->toBe(0);
});

it('creates an author and refuses to delete one with stories', function () {
    $this->actingAs(admin())->post('/admin/authors', [
        'name' => 'Sam Okafor',
        'bio' => 'Covers robotics.',
    ])->assertRedirect();

    expect(Author::query()->where('name', 'Sam Okafor')->exists())->toBeTrue();

    $article = Article::factory()->create();

    $this->actingAs(admin())
        ->delete("/admin/authors/{$article->author_id}")
        ->assertSessionHas('error');
});

/*
|--------------------------------------------------------------------------
| Subscribers
|--------------------------------------------------------------------------
*/

it('lists, removes and exports subscribers', function () {
    Subscriber::query()->create(['email' => 'reader@example.com', 'subscribed_at' => now()]);

    $this->actingAs(admin())->get('/admin/subscribers')
        ->assertOk()
        ->assertSee('reader@example.com');

    $export = $this->actingAs(admin())->get('/admin/subscribers/export');
    $export->assertOk()->assertHeader('content-type', 'text/csv; charset=utf-8');
    expect($export->streamedContent())->toContain('reader@example.com');

    $subscriber = Subscriber::query()->firstOrFail();
    $this->actingAs(admin())->delete("/admin/subscribers/{$subscriber->id}")->assertRedirect();

    expect(Subscriber::query()->count())->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
*/

it('saves settings and applies them to the public site', function () {
    Article::factory()->count(3)->create();

    $this->actingAs(admin())->put('/admin/settings', [
        'site_tagline' => 'Reporting the machines',
        'footer_description' => 'A short footer blurb.',
        'articles_per_page' => 3,
        'promo_enabled' => '1',
        'promo_eyebrow' => 'Live',
        'promo_text' => 'We are broadcasting today.',
        'promo_cta_label' => '',
        'promo_cta_url' => '',
        'promo_tone' => 'brand',
        'adsense_client_id' => 'ca-pub-1234567890123456',
    ])->assertRedirect();

    $this->get('/')
        ->assertOk()
        ->assertSee('Reporting the machines')
        ->assertSee('A short footer blurb.')
        ->assertSee('We are broadcasting today.')
        // The AdSense loader appears once a publisher id is saved.
        ->assertSee('adsbygoogle.js?client=ca-pub-1234567890123456', escape: false);
});

it('hides the promo strip and the adsense script by default', function () {
    Article::factory()->create();

    // Asserting on the script host, not the filename — the ad-slot placeholder
    // comment mentions "adsbygoogle.js" as a note for whoever wires it up.
    $this->get('/')
        ->assertOk()
        ->assertDontSee('pagead2.googlesyndication.com', escape: false)
        ->assertDontSee('role="region" aria-label="Announcement"', escape: false);
});

it('rejects a malformed adsense publisher id', function () {
    $this->actingAs(admin())->put('/admin/settings', [
        'site_tagline' => 'Tagline',
        'footer_description' => 'Blurb.',
        'articles_per_page' => 8,
        'promo_tone' => 'accent',
        'adsense_client_id' => 'pub-not-valid',
    ])->assertSessionHasErrors('adsense_client_id');
});

it('does not let a crafted payload invent new settings', function () {
    $this->actingAs(admin())->put('/admin/settings', [
        'site_tagline' => 'Tagline',
        'footer_description' => 'Blurb.',
        'articles_per_page' => 8,
        'promo_tone' => 'accent',
        'evil_key' => 'evil value',
    ])->assertRedirect();

    expect(Setting::query()->whereKey('evil_key')->exists())->toBeFalse();
});
