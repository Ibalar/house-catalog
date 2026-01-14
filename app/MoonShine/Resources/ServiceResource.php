<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Service;
use App\MoonShine\Field\TinyMCEField;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Routes\ResourceComponents\CrudComponents;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\BelongsTo;
use MoonShine\UI\Fields\File;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Slug;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use MoonShine\UI\Decorations\Tab;
use MoonShine\UI\Decorations\Tabs;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Support\Attributes\Icon;

#[Icon('heroicons.list-bullet')]
class ServiceResource extends ModelResource
{
    protected string $model = Service::class;

    protected string $column = 'title';

    public function getIcon(): string
    {
        return 'heroicons.list-bullet';
    }

    public function getSortColumn(): string
    {
        return 'sort_order';
    }

    /**
     * @return list<FieldContract|ComponentContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Title', 'title'),
            Slug::make('Slug', 'slug'),
            Text::make('Parent', 'parent.title'),
            Switcher::make('Published', 'is_published'),
            Number::make('Sort Order', 'sort_order')->sortable(),
        ];
    }

    /**
     * @return list<FieldContract|ComponentContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make(
                [
                    Tabs::make([
                        Tab::make('Main', [
                            Text::make('Title', 'title')
                                ->required()
                                ->placeholder('Enter service title'),
                            
                            Slug::make('Slug', 'slug')
                                ->from('title'),
                            
                            BelongsTo::make('Parent Service', 'parent', $this)
                                ->nullable()
                                ->searchable()
                                ->placeholder('Select parent service (leave empty for root)'),
                            
                            Textarea::make('Description', 'description')
                                ->placeholder('Short description'),
                            
                            TinyMCEField::make('Full Text', 'full_text')
                                ->placeholder('Enter full service description'),
                        ]),
                        
                        Tab::make('Image', [
                            File::make('Image', 'image')
                                ->disk(Storage::disk('public'))
                                ->dir('/services')
                                ->allowedExtensions(['jpg', 'jpeg', 'png', 'webp'])
                                ->removable(),
                        ]),
                        
                        Tab::make('Settings', [
                            Number::make('Sort Order', 'sort_order')
                                ->nullable()
                                ->default(0),
                            
                            Switcher::make('Published', 'is_published'),
                            
                            Textarea::make('Meta Fields (JSON)', 'meta_fields')
                                ->placeholder('JSON format: {"key": "value"}'),
                        ]),
                    ]),
                ]
            ),
        ];
    }

    /**
     * @return list<FieldContract|ComponentContract>
     */
    protected function detailFields(): iterable
    {
        return $this->indexFields();
    }

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [
            Text::make('Title', 'title'),
            Switcher::make('Published', 'is_published'),
        ];
    }

    protected function afterUpdated($item): void
    {
        $this->cleanupOldFiles($item);
    }

    protected function rules($item): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:services,slug,' . ($item?->id ?? '')],
            'parent_id' => ['nullable', 'exists:services,id'],
        ];
    }

    private function cleanupOldFiles(Service $service): void
    {
        $oldFile = $service->getOriginal('image');
        $newFile = $service->getAttribute('image');
        
        if (!empty($oldFile) && $oldFile !== $newFile) {
            Storage::disk('public')->delete($oldFile);
        }
    }
}