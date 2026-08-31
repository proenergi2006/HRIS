<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('contract:remind')->dailyAt('08:00');
Schedule::command('approval:remind-overdue')->dailyAt('08:30');
Schedule::command('backup:database')->dailyAt('01:00');
Schedule::command('documents:remind')->dailyAt('08:15');
Schedule::command('leave:year-end')->yearlyOn(1, 1, '02:00');
Schedule::command('leave:expire-carry')->dailyAt('02:30');
