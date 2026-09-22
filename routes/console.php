<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('recap:unreturned --send')->dailyAt(
    (function (): string {
        try {
            $time = \App\Models\Setting::get('recap_time', '16:00');
            return preg_match('/^\d{2}:\d{2}$/', (string) $time) ? (string) $time : '16:00';
        } catch (\Throwable) {
            return '16:00';
        }
    })()
);
