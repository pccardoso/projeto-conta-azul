<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

//Schedule::command('app:refresh-token-command')->cron('*/55 * * * *');

//Schedule::command('app:validate-financial-command')->cron('*/10 * * * *');