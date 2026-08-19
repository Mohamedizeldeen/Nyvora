<?php

use App\Models\Category;

/**
 * Error pages must actually be errors.
 *
 * Two real bugs sat here: the contact modal in the layout reads $errors, which
 * Laravel only shares inside the `web` middleware group — so a URL matching no
 * route at all rendered a 500. And the header called ->is() on the {category}
 * route parameter, which is still a raw string when model binding has failed.
 *
 * Both turned ordinary 404s into 500s, which readers see and search engines
 * record. These assertions are cheap; the bugs were not.
 */
it('returns 404, not 500, for anything that does not exist', function (string $path) {
    // A category has to exist for the header and the 404 page to have chips to render.
    Category::factory()->create(['name' => 'Gadgets', 'slug' => 'gadgets']);

    $this->get($path)->assertNotFound();
})->with([
    'no matching route' => '/nonexistent',
    'a file-looking path' => '/ads.txt',
    'nested path' => '/foo/bar',
    'unknown category' => '/category/no-such-section',
    'unknown category, paginated' => '/category/no-such-section?page=2',
    'unknown article' => '/article/no-such-story',
    'unknown author' => '/author/no-such-person',
    'bad newsletter token' => '/newsletter/confirm/not-a-real-token',
    'unknown admin path' => '/admin/no-such-screen',
]);

it('still renders the branded 404 page on a route that matched nothing', function () {
    Category::factory()->create(['name' => 'Security', 'slug' => 'security']);

    $this->get('/nonexistent')
        ->assertNotFound()
        ->assertSee('We cannot find that page')
        ->assertSee('Security')
        ->assertSee('noindex, follow', escape: false);
});
