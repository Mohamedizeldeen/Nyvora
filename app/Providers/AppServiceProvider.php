<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\MailgunList;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\ViewErrorBag;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Built from config rather than by autowiring — the constructor's
        // defaults would otherwise hand every caller an unconfigured client.
        $this->app->singleton(MailgunList::class, fn () => MailgunList::fromConfig());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Laravel shares $errors through the `web` middleware group. Error
        // pages rendered *outside* it — a 404 for a URL that matches no route
        // at all — never get it, so any component touching $errors turns that
        // 404 into a 500. Sharing an empty bag up front makes every view safe;
        // the middleware still overwrites it with the real one per request.
        ViewFacade::share('errors', new ViewErrorBag);

        // The navbar and footer both list every section. Composing the data here
        // keeps that query out of every controller that renders the chrome.
        ViewFacade::composer(
            ['components.header', 'components.footer'],
            fn (View $view) => $view->with(
                'navCategories',
                Category::query()->orderBy('name')->get(),
            ),
        );

        // Pagination links and canonical URLs should keep the https scheme when
        // the app runs behind a TLS-terminating proxy in production.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
