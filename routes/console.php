<?php

Schedule::command('remonline:fetch-orders')
    ->everyMinute();

Schedule::command('issues:escalate')
    ->everyMinute()
    ->between('9:00', '21:00')
    ->withoutOverlapping();
Schedule::command('issues:escalate --postponed')
    ->dailyAt('21:00');

Schedule::command('remonline:check-potential-alert')
    ->everyMinute();
Schedule::command('remonline:potential-autocall')
    ->everyMinute()
->between('8:00', '23:00');
