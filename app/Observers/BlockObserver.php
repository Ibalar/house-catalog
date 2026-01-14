<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Block;
use Illuminate\Support\Facades\Cache;

class BlockObserver
{
    public function saved(Block $block): void
    {
        Cache::forget("block_{$block->name}");
        Cache::forget('blocks_all');
    }

    public function deleted(Block $block): void
    {
        Cache::forget("block_{$block->name}");
        Cache::forget('blocks_all');
    }
}
