<?php

use App\Console\Commands\MakeService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


// retistering make service class command
Artisan::command('make:service {name}', function ($name) {
    $this->call(MakeService::class, ['name' => $name]);
});