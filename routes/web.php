<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriberController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
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
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('newsletter.unsubscribe');

/*
|--------------------------------------------------------------------------
| Static pages
|--------------------------------------------------------------------------
|
| AdSense review expects a publisher to have these three pages live.
|
*/

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy-policy');

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

Route::middleware(['auth', 'admin'])
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

        Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
    });
