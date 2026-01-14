<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Page;
use Illuminate\Support\Facades\Cache;

class PageObserver
{
    public function saved(Page $page): void
    {
        Cache::forget('blocks_all');
    }

    public function deleted(Page $page): void
    {
        Cache::forget('blocks_all');
    }
}
