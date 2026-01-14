<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Block;
use App\MoonShine\Field\TinyMCEField;
use Illuminate\Support\Facades\Storage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\File;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Slug;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Components\Text as TextComponent;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Support\Attributes\Icon;

#[Icon('heroicons.squares-plus')]
class BlockResource extends ModelResource
{
    protected string $model = Block::class;

    protected string $column = 'title';

    public function getIcon(): string
    {
        return 'heroicons.squares-plus';
    }

    /**
     * @return list<FieldContract|ComponentContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Name', 'name'),
            Text::make('Title', 'title'),
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
                Slug::make('Name', 'name')
                    ->required()
                    ->unique()
                    ->hint('Used as @block("name") in content'),
                
                Text::make('Title', 'title')
                    ->required()
                    ->placeholder('Enter block title'),
                
                TinyMCEField::make('Content', 'content')
                    ->placeholder('Enter block content with TinyMCE'),
                
                File::make('Image', 'image')
                    ->disk(Storage::disk('public'))
                    ->dir('/blocks')
                    ->allowedExtensions(['jpg', 'jpeg', 'png', 'webp'])
                    ->removable(),
                
                Text::make('Link', 'link')
                    ->nullable()
                    ->placeholder('Enter link URL (optional)'),
                
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
            Text::make('Name', 'name'),
            Text::make('Title', 'title'),
            Switcher::make('Active', 'is_active'),
        ];
    }

    protected function afterUpdated($item): void
    {
        $this->cleanupOldFiles($item);
    }

    protected function rules($item): array
    {
        return [
            'name' => ['required', 'string', 'regex:/^[a-z0-9_-]+$/', 'unique:blocks,name,' . ($item?->id ?? ''), 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:500'],
        ];
    }

    private function cleanupOldFiles(Block $block): void
    {
        $oldFile = $block->getOriginal('image');
        $newFile = $block->getAttribute('image');
        
        if (!empty($oldFile) && $oldFile !== $newFile) {
            Storage::disk('public')->delete($oldFile);
        }
    }
}