<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Backup basis data otomatis setiap malam pukul 23.00.
// Di server, aktifkan dengan satu cron: `php artisan schedule:run` tiap menit
// (lihat DEPLOY.md). withoutOverlapping mencegah tumpang tindih bila proses lama.
Schedule::command('backup:database')
    ->dailyAt('23:00')
    ->withoutOverlapping();
