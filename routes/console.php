<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// On the public demo, reset to clean demo data every night so it can't stay vandalised.
if (config('app.demo_mode')) {
    Schedule::command('demo:reset --force')->dailyAt('03:00');
}
