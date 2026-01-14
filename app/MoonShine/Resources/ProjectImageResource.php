<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\ProjectImage;
use Illuminate\Support\Facades\Storage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\File;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Support\Attributes\Icon;

#[Icon('heroicons.photo')]
class ProjectImageResource extends ModelResource
{
    protected string $model = ProjectImage::class;

    protected string $column = 'sort_order';

    public function getIcon(): string
    {
        return 'heroicons.photo';
    }

    /**
     * @return list<FieldContract|ComponentContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            File::make('Image', 'image_path')
                ->disk(Storage::disk('public'))
                ->dir('/projects'),
            Number::make('Sort Order', 'sort_order')->sortable(),
        ];
    }

    /**
     * @return list<FieldContract|ComponentContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                File::make('Image', 'image_path')
                    ->disk(Storage::disk('public'))
                    ->dir('/projects')
                    ->allowedExtensions(['jpg', 'jpeg', 'png', 'webp'])
                    ->removable(),
                
                Number::make('Sort Order', 'sort_order')
                    ->nullable()
                    ->default(0),
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
            Number::make('Sort Order', 'sort_order'),
        ];
    }

    protected function rules($item): array
    {
        return [
            'image_path' => ['required'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function afterUpdated($item): void
    {
        $this->cleanupOldFiles($item);
    }

    private function cleanupOldFiles(ProjectImage $image): void
    {
        $oldFile = $image->getOriginal('image_path');
        $newFile = $image->getAttribute('image_path');
        
        if (!empty($oldFile) && $oldFile !== $newFile) {
            Storage::disk('public')->delete($oldFile);
        }
    }
}