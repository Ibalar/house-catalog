<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Project;
use Illuminate\Support\Facades\Cache;

class ProjectObserver
{
    public function saved(Project $project): void
    {
        Cache::forget('projects_categories');
        Cache::forget('projects_available_values');
        Cache::forget('blocks_all');
    }

    public function deleted(Project $project): void
    {
        Cache::forget('projects_categories');
        Cache::forget('projects_available_values');
        Cache::forget('blocks_all');
    }
}
