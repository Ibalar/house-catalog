<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Resources\BlockResource;
use App\MoonShine\Resources\LeadResource;
use App\MoonShine\Resources\PageResource;
use App\MoonShine\Resources\ProjectCategoryResource;
use App\MoonShine\Resources\ProjectResource;
use App\MoonShine\Resources\ServiceResource;
use App\MoonShine\Resources\SettingResource;
use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;
use MoonShine\Laravel\Resources\MoonShineUserResource;
use MoonShine\Laravel\Resources\MoonShineUserRoleResource;

class MoonShineServiceProvider extends ServiceProvider
{
    /**
     * @param  CoreContract<MoonShineConfigurator>  $core
     */
    public function boot(CoreContract $core): void
    {
        $core
            ->resources([
                // Users and Roles (default MoonShine)
                MoonShineUserResource::class,
                MoonShineUserRoleResource::class,
                
                // Content Management
                PageResource::class,
                BlockResource::class,
                
                // Catalog
                ServiceResource::class,
                ProjectCategoryResource::class,
                ProjectResource::class,
                
                // Management
                LeadResource::class,
                SettingResource::class,
            ])
            ->pages([
                ...$core->getConfig()->getPages(),
            ])
        ;
    }
}
