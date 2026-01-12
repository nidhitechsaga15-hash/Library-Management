<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule library card expiration check (daily at 9 AM)
Schedule::command('library-cards:check-expiration')->dailyAt('09:00');

// Schedule book reminders (daily at 10 AM)
Schedule::command('books:send-reminders')->dailyAt('10:00');

// Schedule daily book reminders (due tomorrow and due today) - daily at 9 AM
Schedule::command('books:daily-reminders')->dailyAt('09:00');

// Schedule auto-return expired book requests (daily at 11 AM)
Schedule::command('books:auto-return-expired')->dailyAt('11:00');

// Schedule auto-cancel expired holds (every 5 minutes for immediate stock return)
Schedule::command('book-holds:auto-cancel')->everyFiveMinutes();

// Schedule auto-create fines for overdue books (daily at 8 AM)
Schedule::command('fines:auto-create-overdue')->dailyAt('08:00');
