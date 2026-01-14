<?php

namespace App\Providers;

use App\Models\Block;
use App\Models\Page;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Service;
use App\Observers\BlockObserver;
use App\Observers\PageObserver;
use App\Observers\ProjectObserver;
use App\Observers\SettingObserver;
use App\Observers\ServiceObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        require_once app_path('Helpers/helpers.php');

        Page::observe(PageObserver::class);
        Service::observe(ServiceObserver::class);
        Project::observe(ProjectObserver::class);
        Block::observe(BlockObserver::class);
        Setting::observe(SettingObserver::class);
    }
}
