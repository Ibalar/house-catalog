<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Page;
use App\MoonShine\Field\TinyMCEField;
use Illuminate\Support\Facades\Storage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Slug;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Textarea;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;

#[Icon('heroicons.book-open')]
class PageResource extends ModelResource
{
    protected string $model = Page::class;

    protected string $column = 'title';

    public function getIcon(): string
    {
        return 'heroicons.book-open';
    }

    public function getSortColumn(): string
    {
        return 'created_at';
    }

    public function getFormRedirect(): string
    {
        return 'index';
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
            Switcher::make('Active', 'is_active'),
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
                Text::make('Title', 'title')
                    ->required()
                    ->placeholder('Enter page title'),
                
                Slug::make('Slug', 'slug')
                    ->from('title'),
                
                TinyMCEField::make('Content', 'content')
                    ->placeholder('Enter page content. You can use @block("name") to insert blocks.'),
                
                Text::make('Meta Title', 'meta_title')
                    ->placeholder('Enter meta title (optional)'),
                
                Textarea::make('Meta Description', 'meta_description')
                    ->placeholder('Enter meta description (optional)'),
                
                Switcher::make('Active', 'is_active'),
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
            Text::make('Title', 'title'),
            Switcher::make('Active', 'is_active'),
        ];
    }

    protected function afterUpdated($item): void
    {
        // Handle old file cleanup if needed
    }

    protected function rules($item): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:pages,slug,' . ($item?->id ?? '')],
            'meta_title' => ['nullable', 'string', 'max:255'],
        ];
    }
}