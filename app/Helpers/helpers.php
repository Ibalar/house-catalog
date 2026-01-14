<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

if (!function_exists('get_block')) {
    function get_block(string $name): string
    {
        return Cache::remember("block_{$name}", now()->addHour(), function () use ($name) {
            $block = \App\Models\Block::where('name', $name)
                ->where('is_active', true)
                ->first();

            return $block ? $block->content : '';
        });
    }
}

if (!function_exists('get_setting')) {
    function get_setting(string $key, $default = null): ?string
    {
        return Cache::remember("setting_{$key}", now()->addDay(), function () use ($key, $default) {
            $setting = \App\Models\Setting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }
}

if (!function_exists('get_asset_url')) {
    function get_asset_url(?string $path): string
    {
        if (empty($path)) {
            return asset('images/placeholder.jpg');
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return Storage::url($path);
    }
}

if (!function_exists('get_resized_image')) {
    function get_resized_image(?string $path, int $width = 800, int $height = 600): string
    {
        if (empty($path)) {
            return asset('images/placeholder.jpg');
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // Try to get resized version first
        $pathInfo = pathinfo($path);
        $extension = $pathInfo['extension'] ?? 'jpg';
        $resizedPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . "_{$width}x{$height}.{$extension}";

        if (Storage::exists($resizedPath)) {
            return Storage::url($resizedPath);
        }

        return Storage::url($path);
    }
}

if (!function_exists('format_phone')) {
    function format_phone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) === 11) {
            return '+' . substr($phone, 0, 1) . ' (' . substr($phone, 1, 3) . ') ' . substr($phone, 4, 3) . '-' . substr($phone, 7, 2) . '-' . substr($phone, 9, 2);
        }

        return $phone;
    }
}
