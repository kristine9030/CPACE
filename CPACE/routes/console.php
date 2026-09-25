<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Proctoring frames are photographs of students: sweep whatever survived the
// submit-time cleanup once the retention window has passed. Needs the host's
// cron to run `php artisan schedule:run` every minute.
Schedule::command('mock-exam:purge-captures')->dailyAt('02:00');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
