<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Block;

class BlockHelper
{
    public static function getBlockContent(string $name): string
    {
        $block = Block::where('name', $name)
            ->where('is_active', true)
            ->first();

        return $block ? $block->content : '';
    }
}

if (!function_exists('get_block')) {
    function get_block(string $name): string
    {
        return BlockHelper::getBlockContent($name);
    }
}