<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Schedule::command('halltickets:cleanup')->hourly();

// Automatically process queued jobs (PDF generation, emails, default jobs) every minute via cron schedule:run
\Illuminate\Support\Facades\Schedule::command('queue:work --queue=pdf,default --stop-when-empty --tries=3 --max-time=50')
    ->everyMinute()
    ->withoutOverlapping();

// Pre-warm and rotate WebRTC TURN credentials daily to prevent credential expiry
\Illuminate\Support\Facades\Schedule::command('webrtc:warmup-turn-credentials')
    ->daily();


