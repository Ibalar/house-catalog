<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Block;
use App\Models\Setting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class BlockServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerBladeDirectives();
        $this->registerHelpers();
    }

    private function registerBladeDirectives(): void
    {
        Blade::directive('block', function ($expression) {
            return "<?php echo App\\Helpers\\BlockHelper::getBlockContent({$expression}) ?>";
        });
    }

    private function registerHelpers(): void
    {
        // Helper for blocks
        if (!function_exists('get_block')) {
            function get_block(string $name): string
            {
                return BlockHelper::getBlockContent($name);
            }
        }

        // Helper for settings
        if (!function_exists('get_setting')) {
            function get_setting(string $key, string $default = ''): string
            {
                $setting = Setting::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            }
        }
    }
}