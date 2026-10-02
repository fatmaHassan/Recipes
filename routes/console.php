<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily TheMealDB sync (UTC 03:00 = 6am AST)
Schedule::command('app:mealdb:sync')->dailyAt('03:00');
