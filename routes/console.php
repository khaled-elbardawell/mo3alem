<?php

use App\Models\SmartBoardSession;
use App\Models\UniqueMetricEvent;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => UniqueMetricEvent::query()
    ->whereDate('date', '<', today())
    ->delete())
    ->name('metrics:prune-unique-events')
    ->dailyAt('00:15')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('sanctum:prune-expired --hours=24')
    ->dailyAt('00:30')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::call(fn () => SmartBoardSession::query()
    ->where('expires_at', '<', now()->subDay())
    ->delete())
    ->name('smart-boards:prune-sessions')
    ->dailyAt('00:45')
    ->withoutOverlapping()
    ->onOneServer();
