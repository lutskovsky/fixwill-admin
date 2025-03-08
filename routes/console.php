<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Schedule::command('remonline:fetch-orders')->everyMinute();
Schedule::command('issues:escalate')
    ->everyMinute()
    ->between('9:00', '23:30')
    ->withoutOverlapping();
Schedule::command('issues:escalate --postponed')
    ->dailyAt('21:00');
