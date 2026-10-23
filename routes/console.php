<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Unpaid registrations whose Snap page lapsed give their quota seat back
Schedule::command('payments:release-lapsed')->everyTenMinutes()->withoutOverlapping();
