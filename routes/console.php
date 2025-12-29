<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the alert generator command to run daily at 9:00 AM
// This ensures alerts are generated once per day on a single server instance
Schedule::command('alerts:generate')
    ->dailyAt('09:00')
    ->onOneServer()
    ->withoutOverlapping()
    ->runInBackground();
