<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Text as TextComponent;
use MoonShine\UI\Decorations\Tab;
use MoonShine\UI\Decorations\Tabs;
use MoonShine\UI\Fields\File;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Hidden;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use MoonShine\UI\Fields\Switcher;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;

#[Icon('heroicons.cog-6-tooth')]
class SettingResource extends ModelResource
{
    protected string $model = Setting::class;

    protected string $column = 'group';

    public function getIcon(): string
    {
        return 'heroicons.cog-6-tooth';
    }

    /**
     * @return list<FieldContract|ComponentContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Key', 'key'),
            Text::make('Value', 'value'),
            Text::make('Group', 'group'),
        ];
    }

    /**
     * @return list<FieldContract|ComponentContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                Tabs::make([
                    Tab::make('General', [
                        $this->createSettingField('site_name', 'Site Name'),
                        $this->createSettingField('site_email', 'Site Email'),
                        $this->createSettingField('site_phone', 'Site Phone'),
                        $this->createSettingField('site_address', 'Site Address'),
                    ]),
                    
                    Tab::make('Social', [
                        $this->createSettingField('social_vk', 'VK URL'),
                        $this->createSettingField('social_telegram', 'Telegram URL'),
                        $this->createSettingField('social_whatsapp', 'WhatsApp URL'),
                        $this->createSettingField('social_instagram', 'Instagram URL'),
                        $this->createSettingField('social_facebook', 'Facebook URL'),
                    ]),
                    
                    Tab::make('Files', [
                        $this->createSettingField('favicon_path', 'Favicon', 'file'),
                        $this->createSettingField('logo_path', 'Logo', 'file'),
                        $this->createSettingField('og_image_path', 'OG Image', 'file'),
                    ]),
                    
                    Tab::make('Other', [
                        $this->createSettingField('default_meta_title', 'Default Meta Title'),
                        $this->createSettingField('default_meta_description', 'Default Meta Description'),
                        $this->createSettingField('google_analytics', 'Google Analytics Code'),
                        $this->createSettingField('yandex_metrika', 'Yandex Metrika Code'),
                    ]),
                ])
            ])
        ];
    }

    /**
     * @return list<FieldContract|ComponentContract>
     */
    protected function detailFields(): iterable
    {
        return $this->indexFields();
    }

    private function createSettingField(string $key, string $label, string $type = 'text'): FieldContract
    {
        $setting = Setting::where('key', $key)->first();
        $readonly = $setting !== null; // readonly after first save

        return match($type) {
            'file' => File::make($label, sprintf('settings[%s]', $key))
                ->disk(Storage::disk('public'))
                ->dir('/settings')
                ->allowedExtensions(['png', 'jpg', 'jpeg', 'webp', 'ico'])
                ->removable(),
            default => Text::make($label, sprintf('settings[%s]', $key))
                ->when($readonly, fn($field) => $field->readonly()),
        };
    }

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [
            Text::make('Group', 'group'),
        ];
    }

    protected function rules($item): array
    {
        return [
            'key' => ['required', 'string', 'max:255', 'unique:settings,key,' . ($item?->id ?? '')],
            'value' => ['nullable', 'string'],
            'group' => ['required', 'in:general,social,files,other'],
        ];
    }

    public function afterUpdated($item): void
    {
        // Handle file cleanup for settings
        $this->cleanupSettingFiles($item);
    }

    private function cleanupSettingFiles(Setting $setting): void
    {
        $oldValue = $setting->getOriginal('value');
        $newValue = $setting->getAttribute('value');
        
        if (!empty($oldValue) && $oldValue !== $newValue) {
            Storage::disk('public')->delete($oldValue);
        }
    }
}