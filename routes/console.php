<?php

use App\Console\Commands\CleanupExportsCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cleanup file export > 24 jam — jalankan setiap hari jam 02:00
Schedule::command(CleanupExportsCommand::class)
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/cleanup-exports.log'));

// Backup database PostgreSQL — jalankan setiap hari jam 01:00
Schedule::command(\App\Console\Commands\BackupDatabaseCommand::class)
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/db-backup.log'));

