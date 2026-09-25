<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Proctoring frames are photographs of students: sweep whatever survived the
// submit-time cleanup once the retention window has passed. Needs the host's
// cron to run `php artisan schedule:run` every minute.
Schedule::command('mock-exam:purge-captures')->dailyAt('02:00');

// Grade sittings a student walked away from without submitting. The monitor and
// the student's own pages also do this on the fly; this catches the rest.
Schedule::command('mock-exam:close-expired')->everyFiveMinutes();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
