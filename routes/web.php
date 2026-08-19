<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AdsTxtController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriberController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Editorial
|--------------------------------------------------------------------------
|
| {category} and {article} are resolved by slug — both models declare
| #[RouteKey('slug')] — so an unknown slug returns a 404 automatically.
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/category/{category}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/article/{article}', [ArticleController::class, 'show'])->name('article.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');

// Reader comments. Held for moderation — see CommentController. Throttled,
// because the form is public and unauthenticated.
Route::post('/article/{article}/comments', [CommentController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('comments.store');

// Bylines. A reporter's name is often what readers search for, so each one
// gets its own indexable page listing everything they have published.
Route::get('/authors', [AuthorController::class, 'index'])->name('authors.index');
Route::get('/author/{author}', [AuthorController::class, 'show'])->name('authors.show');

/*
|--------------------------------------------------------------------------
| SEO endpoints
|--------------------------------------------------------------------------
|
| Generated rather than static so the URLs always match the live domain.
|
*/

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('robots');
Route::get('/feed', [FeedController::class, 'index'])->name('feed');
// Google checks this to confirm who may sell this site's ad inventory.
Route::get('/ads.txt', [AdsTxtController::class, 'index'])->name('ads-txt');
// The human-readable companion to the feed. /feed sends browsers here.
Route::get('/rss', [FeedController::class, 'page'])->name('rss');

/*
|--------------------------------------------------------------------------
| Newsletter
|--------------------------------------------------------------------------
|
| Double opt-in. Signing up sends a confirmation email through Mailgun;
| the confirm and unsubscribe links resolve on an unguessable token, so
| nobody can subscribe or remove somebody else by guessing an id.
|
| Signups are throttled per IP — the form is public and unauthenticated.
|
*/

Route::post('/subscribe', [SubscriberController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('subscribe');

Route::get('/newsletter/confirm/{subscriber}', [NewsletterController::class, 'confirm'])
    ->name('newsletter.confirm');

// GET for the link in the email, POST for the RFC 8058 one-click header.
Route::match(['get', 'post'], '/newsletter/unsubscribe/{subscriber}', [NewsletterController::class, 'unsubscribe'])
    ->withoutMiddleware([PreventRequestForgery::class])
    ->name('newsletter.unsubscribe');

/*
|--------------------------------------------------------------------------
| Static pages
|--------------------------------------------------------------------------
|
| AdSense review — and readers — expect a publication to say who it is,
| how to reach it, how it makes editorial decisions, and what it does with
| their data. All of it lives here.
|
*/

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
// Every form on the site posts here. Throttled — it is public and unauthenticated.
Route::post('/contact', [ContactMessageController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.send');
Route::get('/team', [PageController::class, 'team'])->name('team');
Route::get('/editorial-policy', [PageController::class, 'editorialPolicy'])->name('editorial-policy');
Route::get('/advertise', [PageController::class, 'advertise'])->name('advertise');
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/cookie-policy', [PageController::class, 'cookiePolicy'])->name('cookie-policy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
|
| A single sign-in form for newsroom staff. There is no public registration —
| admins are created with `php artisan nyvora:make-admin`.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
|
| Everything here requires a signed-in user with is_admin = true.
|
*/

// AuthenticateSession is what gives Auth::logoutOtherDevices() teeth: it stamps
// the password hash into the session, so every *other* admin session dies the
// moment the password changes. Without it that call is close to a no-op.
Route::middleware(['auth', AuthenticateSession::class, 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', Admin\DashboardController::class.'@index')->name('dashboard');

        // Quick actions from the story list.
        Route::post('articles/{article}/feature', [Admin\ArticleController::class, 'toggleFeatured'])->name('articles.feature');
        Route::post('articles/{article}/publish', [Admin\ArticleController::class, 'togglePublished'])->name('articles.publish');

        Route::resource('articles', Admin\ArticleController::class)->except('show');
        Route::resource('categories', Admin\CategoryController::class)->except('show');
        Route::resource('authors', Admin\AuthorController::class)->except('show');

        Route::get('subscribers/export', [Admin\SubscriberController::class, 'export'])->name('subscribers.export');
        Route::get('subscribers', [Admin\SubscriberController::class, 'index'])->name('subscribers.index');
        // Bound by id, not the model's route key — the token is a secret that
        // belongs in the reader's email, not in admin URLs and access logs.
        Route::delete('subscribers/{subscriber:id}', [Admin\SubscriberController::class, 'destroy'])->name('subscribers.destroy');

        // The signed-in administrator's own account.
        Route::get('account', [Admin\AccountController::class, 'edit'])->name('account.edit');
        Route::put('account', [Admin\AccountController::class, 'updateProfile'])->name('account.profile');
        Route::put('account/password', [Admin\AccountController::class, 'updatePassword'])->name('account.password');

        Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports.index');

        Route::get('comments', [Admin\CommentController::class, 'index'])->name('comments.index');
        Route::post('comments/{comment}/approve', [Admin\CommentController::class, 'approve'])->name('comments.approve');
        Route::post('comments/{comment}/unapprove', [Admin\CommentController::class, 'unapprove'])->name('comments.unapprove');
        Route::delete('comments/{comment}', [Admin\CommentController::class, 'destroy'])->name('comments.destroy');

        Route::get('messages', [Admin\MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [Admin\MessageController::class, 'show'])->name('messages.show');
        Route::delete('messages/{message}', [Admin\MessageController::class, 'destroy'])->name('messages.destroy');

        Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
    });
