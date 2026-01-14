<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\ProjectImportController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Admin routes for Project CSV import (MoonShine)
Route::middleware(['auth:moonshine'])
    ->prefix('admin/projects')
    ->name('admin.projects.')
    ->group(function () {
        Route::get('/import', [ProjectImportController::class, 'showForm'])->name('import');
        Route::post('/import', [ProjectImportController::class, 'import'])->name('import.store');
    });

Route::prefix('services')->name('services.')->group(function () {
    Route::get('/', [ServiceController::class, 'index'])->name('index');
    Route::get('/{slug}', [ServiceController::class, 'show'])->name('show')->where('slug', '[a-z0-9-]+');
});

Route::prefix('projects')->name('projects.')->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('index');
    Route::get('/{slug}', [ProjectController::class, 'show'])->name('show')->where('slug', '[a-z0-9-]+');
});

Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');

Route::get('/sitemap.xml', function () {
    $sitemapPath = public_path('sitemap.xml');
    if (file_exists($sitemapPath)) {
        return response()->file($sitemapPath, ['Content-Type' => 'application/xml']);
    }
    abort(404);
})->name('sitemap');

Route::get('/robots.txt', function () {
    $content = "User-agent: *\n";
    $content .= "Allow: /\n";
    $content .= "Sitemap: " . url('/sitemap.xml') . "\n";

    return response($content, 200, ['Content-Type' => 'text/plain']);
})->name('robots');

Route::get('/{slug}', [PageController::class, 'show'])->name('page.show')->where('slug', '.*');
