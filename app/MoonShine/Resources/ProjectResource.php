<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Project;
use App\Models\ProjectCategory;
use App\MoonShine\Field\TinyMCEField;
use Illuminate\Support\Facades\Storage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\BelongsTo;
use MoonShine\UI\Fields\Currency;
use MoonShine\UI\Fields\Decimal;
use MoonShine\UI\Fields\File;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Slug;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use MoonShine\UI\Decorations\Tab;
use MoonShine\UI\Decorations\Tabs;
use MoonShine\UI\Decorations\Heading;
use MoonShine\UI\Decorations\Grid;
use MoonShine\UI\Decorations\Column;
use MoonShine\UI\Components\ActionButton;
use MoonShine\Nova\Fields\RangeSlider;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Support\Attributes\Icon;

#[Icon('heroicons.home-modern')]
class ProjectResource extends ModelResource
{
    protected string $model = Project::class;

    protected string $column = 'title';

    public function getIcon(): string
    {
        return 'heroicons.home-modern';
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
            Text::make('Category', 'category.name'),
            Currency::make('Price From', 'price_from')->symbol(''),
            Currency::make('Price To', 'price_to')->symbol(''),
            Switcher::make('Featured', 'is_featured')
                ->badge(fn($item) => $item->is_featured ? 'Yes' : 'No'),
            Switcher::make('Published', 'is_published')
                ->badge(fn($item) => $item->is_published ? 'Yes' : 'No'),
            Text::make('Created At', 'created_at')->sortable(),
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
                    Tab::make('Main', [
                        Grid::make([
                            Column::make([
                                Text::make('Title', 'title')
                                    ->required()
                                    ->placeholder('Enter project title'),
                                
                                Slug::make('Slug', 'slug')
                                    ->from('title'),
                                
                                BelongsTo::make('Category', 'category', ProjectCategoryResource::class)
                                    ->required()
                                    ->searchable()
                                    ->placeholder('Select category'),
                                
                                Textarea::make('Description', 'description')
                                    ->placeholder('Short project description'),
                            ])->columnSpan(9),
                        ]),
                    ]),
                    
                    Tab::make('Price', [
                        Grid::make([
                            Column::make([
                                Currency::make('Price From', 'price_from')
                                    ->symbol('$')
                                    ->nullable()
                                    ->placeholder('0.00'),
                                
                                Currency::make('Price To', 'price_to')
                                    ->symbol('$')
                                    ->nullable()
                                    ->placeholder('0.00'),
                            ])->columnSpan(6),
                        ]),
                    ]),
                    
                    Tab::make('Specifications', [
                        Grid::make([
                            Column::make([
                                Decimal::make('Area (sq.m)', 'area')
                                    ->nullable()
                                    ->placeholder('0.00'),
                                
                                Number::make('Floors', 'floors')
                                    ->nullable()
                                    ->min(1)
                                    ->placeholder('1'),
                                
                                Number::make('Bedrooms', 'bedrooms')
                                    ->nullable()
                                    ->min(0)
                                    ->placeholder('0'),
                                
                                Number::make('Bathrooms', 'bathrooms')
                                    ->nullable()
                                    ->min(0)
                                    ->placeholder('0'),
                                
                                Text::make('Roof Type', 'roof_type')
                                    ->nullable()
                                    ->placeholder('e.g., Gable, Hip'),
                                
                                Text::make('Style', 'style')
                                    ->nullable()
                                    ->placeholder('e.g., Modern, Classic'),
                                
                                Switcher::make('Has Garage', 'has_garage'),
                            ])->columnSpan(6),
                        ]),
                    ]),
                    
                    Tab::make('Images', [
                        Grid::make([
                            Column::make([
                                File::make('Main Image', 'main_image')
                                    ->disk(Storage::disk('public'))
                                    ->dir('/projects')
                                    ->allowedExtensions(['jpg', 'jpeg', 'png', 'webp'])
                                    ->removable(),
                            ])->columnSpan(12),
                        ]),
                    ]),
                    
                    Tab::make('SEO', [
                        Grid::make([
                            Column::make([
                                Text::make('Meta Title', 'meta_title')
                                    ->nullable()
                                    ->placeholder('Enter meta title'),
                                
                                Textarea::make('Meta Description', 'meta_description')
                                    ->nullable()
                                    ->placeholder('Enter meta description'),
                            ])->columnSpan(8),
                        ]),
                    ]),
                    
                    Tab::make('Publication', [
                        Grid::make([
                            Column::make([
                                Switcher::make('Featured', 'is_featured'),
                                
                                Switcher::make('Published', 'is_published'),
                                
                                Number::make('Sort Order', 'sort_order')
                                    ->nullable()
                                    ->default(0),
                            ])->columnSpan(6),
                        ]),
                    ]),
                ]),
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

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [
            BelongsTo::make('Category', 'category', ProjectCategoryResource::class)
                ->nullable(),
            
            Switcher::make('Featured', 'is_featured'),
            
            Switcher::make('Published', 'is_published'),
            
            Text::make('Price From', 'price_from'),
            
            Text::make('Price To', 'price_to'),
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
            'slug' => ['nullable', 'string', 'max:255', 'unique:projects,slug,' . ($item?->id ?? '')],
            'category_id' => ['required', 'exists:project_categories,id'],
            'price_from' => ['nullable', 'numeric', 'min:0'],
            'price_to' => ['nullable', 'numeric', 'min:0'],
            'area' => ['nullable', 'numeric', 'min:0'],
            'floors' => ['nullable', 'integer', 'min:1'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function cleanupOldFiles(Project $project): void
    {
        $oldFile = $project->getOriginal('main_image');
        $newFile = $project->getAttribute('main_image');
        
        if (!empty($oldFile) && $oldFile !== $newFile) {
            Storage::disk('public')->delete($oldFile);
        }
    }
}