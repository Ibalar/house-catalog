<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Service;
use Illuminate\Support\Facades\Cache;

class ServiceObserver
{
    public function saved(Service $service): void
    {
        Cache::forget('services_all');
        Cache::forget('services_root');
        Cache::forget('blocks_all');
    }

    public function deleted(Service $service): void
    {
        Cache::forget('services_all');
        Cache::forget('services_root');
        Cache::forget('blocks_all');
    }
}
