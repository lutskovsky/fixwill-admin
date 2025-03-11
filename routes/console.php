<?php

Schedule::command('remonline:fetch-orders')->everyMinute()
    ->withoutOverlapping();
Schedule::command('issues:escalate')
    ->everyMinute()
    ->between('9:00', '21:00')
    ->withoutOverlapping();
Schedule::command('issues:escalate --postponed')
    ->dailyAt('21:00');
