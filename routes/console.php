<?php

use App\Models\Subscriber;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
|
| Requires the scheduler to be running in production:
|   * * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
|
*/

// Deletes newsletter signups that were never confirmed after 30 days.
// See Subscriber::prunable() — the privacy policy promises this deletion, so
// it has to actually happen.
Schedule::command('model:prune', ['--model' => [Subscriber::class]])
    ->daily()
    ->description('Erase unconfirmed newsletter signups older than 30 days');
