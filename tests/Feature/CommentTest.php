<?php

use App\Models\Article;
use App\Models\Comment;
use App\Models\Setting;
use App\Models\User;

/**
 * Readers leave a name and a comment. Nothing they write appears on the site
 * until an administrator approves it.
 */
function comment(Article $article, array $attributes = []): Comment
{
    return $article->comments()->create(array_merge([
        'name' => 'Layla Hassan',
        'body' => 'Good piece. Passkey support on older Android is still the weak link.',
    ], $attributes));
}

/*
|--------------------------------------------------------------------------
| Leaving a comment
|--------------------------------------------------------------------------
*/

it('accepts a name and a comment, and holds it for approval', function () {
    $article = article();

    $this->post("/article/{$article->slug}/comments", [
        'name' => 'Layla Hassan',
        'body' => 'Passkey support on older Android is still the weak link.',
    ])->assertRedirect()->assertSessionHas('comment_posted');

    $stored = Comment::query()->firstOrFail();

    expect($stored->name)->toBe('Layla Hassan')
        ->and($stored->article_id)->toBe($article->id)
        ->and($stored->isApproved())->toBeFalse();
});

it('never shows an unapproved comment to readers', function () {
    $article = article();
    comment($article, ['name' => 'Waiting Reader', 'body' => 'This should not be public yet.']);

    $this->get("/article/{$article->slug}")
        ->assertOk()
        ->assertDontSee('Waiting Reader')
        ->assertDontSee('This should not be public yet.')
        ->assertSee('No comments yet');
});

it('shows a comment once it is approved', function () {
    $article = article();
    $pending = comment($article, ['name' => 'Layla Hassan']);

    $pending->approve();

    $this->get("/article/{$article->slug}")
        ->assertOk()
        ->assertSee('Layla Hassan')
        ->assertSee('1 comment');
});

it('escapes anything a commenter tries to inject', function () {
    $article = article();
    comment($article, [
        'name' => '<img src=x onerror=alert(1)>',
        'body' => '<script>alert("xss")</script>',
        'approved_at' => now(),
    ]);

    $body = $this->get("/article/{$article->slug}")->assertOk()->getContent();

    expect($body)->not->toContain('<script>alert("xss")</script>')
        ->and($body)->not->toContain('<img src=x onerror')
        ->and($body)->toContain('&lt;script&gt;');
});

it('rejects an incomplete comment', function () {
    $article = article();

    $this->post("/article/{$article->slug}/comments", ['name' => 'A', 'body' => ''])
        ->assertSessionHasErrors(['name', 'body']);

    expect(Comment::query()->count())->toBe(0);
});

it('silently refuses anything that fills the honeypot', function () {
    $article = article();

    $this->post("/article/{$article->slug}/comments", [
        'name' => 'Spam Bot',
        'body' => 'Buy cheap things from my website right now.',
        'website' => 'http://spam.example',
    ])->assertSessionHasErrors('website');

    expect(Comment::query()->count())->toBe(0);
});

it('throttles a flood of comments from one connection', function () {
    $article = article();

    $post = fn () => $this->post("/article/{$article->slug}/comments", [
        'name' => 'Flood Test',
        'body' => 'A repeated comment used to exercise the rate limiter.',
    ]);

    for ($i = 0; $i < 5; $i++) {
        $post()->assertRedirect();
    }

    $post()->assertStatus(429);
});

it('refuses comments on a story that is not published', function () {
    $draft = Article::factory()->draft()->create();

    $this->post("/article/{$draft->slug}/comments", [
        'name' => 'Early Bird',
        'body' => 'Trying to comment on something unpublished.',
    ])->assertNotFound();

    expect(Comment::query()->count())->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Turning comments off
|--------------------------------------------------------------------------
*/

it('closes comments on one story without touching the others', function () {
    $closed = article(['comments_open' => false]);
    $open = article(['comments_open' => true]);

    // Already-approved comments stay readable on the closed story.
    comment($closed, ['name' => 'Earlier Reader', 'approved_at' => now()]);

    $this->get("/article/{$closed->slug}")
        ->assertOk()
        ->assertSee('Earlier Reader')
        ->assertSee('Comments are closed on this story')
        ->assertDontSee('Leave a comment');

    $this->get("/article/{$open->slug}")->assertOk()->assertSee('Leave a comment');

    $this->post("/article/{$closed->slug}/comments", [
        'name' => 'Late Reader', 'body' => 'Trying to comment on a closed story.',
    ])->assertForbidden();
});

it('closes comments site-wide from settings', function () {
    $article = article();
    Setting::put(['comments_enabled' => '0']);

    $this->get("/article/{$article->slug}")->assertOk()->assertDontSee('Leave a comment');

    $this->post("/article/{$article->slug}/comments", [
        'name' => 'Reader', 'body' => 'Trying to comment while comments are off.',
    ])->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Moderation
|--------------------------------------------------------------------------
*/

it('keeps moderation behind the admin gate', function () {
    $article = article();
    comment($article);

    $this->get('/admin/comments')->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get('/admin/comments')->assertForbidden();
});

it('lets an administrator approve, hide and delete', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $article = article();
    $pending = comment($article);

    // The queue is what the admin lands on.
    $this->actingAs($admin)->get('/admin/comments')
        ->assertOk()
        ->assertSee('Layla Hassan')
        ->assertSee('Waiting');

    $this->actingAs($admin)->post("/admin/comments/{$pending->id}/approve")->assertRedirect();
    expect($pending->fresh()->isApproved())->toBeTrue();

    $this->actingAs($admin)->post("/admin/comments/{$pending->id}/unapprove")->assertRedirect();
    expect($pending->fresh()->isApproved())->toBeFalse();

    $this->actingAs($admin)->delete("/admin/comments/{$pending->id}")->assertRedirect();
    expect(Comment::query()->count())->toBe(0);
});

it('separates the waiting queue from what is published', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $article = article();

    comment($article, ['name' => 'Still Waiting']);
    comment($article, ['name' => 'Already Live', 'approved_at' => now()]);

    $this->actingAs($admin)->get('/admin/comments?status=pending')
        ->assertOk()->assertSee('Still Waiting')->assertDontSee('Already Live');

    $this->actingAs($admin)->get('/admin/comments?status=approved')
        ->assertOk()->assertSee('Already Live')->assertDontSee('Still Waiting');
});

it('removes a story\'s comments along with the story', function () {
    $article = article();
    comment($article, ['approved_at' => now()]);

    $article->delete();

    expect(Comment::query()->count())->toBe(0);
});

it('tells readers in the privacy policy that comments are published', function () {
    $this->get('/privacy-policy')
        ->assertOk()
        ->assertSee('Comments')
        ->assertSee('published on the article for anyone to read', escape: false);
});
