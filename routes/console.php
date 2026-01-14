<?php

use App\Console\Commands\GenerateSitemap;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register custom commands
Artisan::command('sitemap:generate', function () {
    $this->call(GenerateSitemap::class);
})->purpose('Generate sitemap.xml');
