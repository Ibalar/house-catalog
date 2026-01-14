<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\ProjectCategory;
use Illuminate\Support\Facades\DB;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Radio;
use MoonShine\UI\Fields\Slug;
use MoonShine\UI\Fields\Text;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Support\Attributes\Icon;

#[Icon('heroicons.squares-2x2')]
class ProjectCategoryResource extends ModelResource
{
    protected string $model = ProjectCategory::class;

    protected string $column = 'name';

    public function getIcon(): string
    {
        return 'heroicons.squares-2x2';
    }

    /**
     * @return list<FieldContract|ComponentContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Name', 'name'),
            Slug::make('Slug', 'slug'),
            Text::make('Type', 'type'),
            Text::make('Projects Count', 'project_count')
                ->badge(fn($item) => $this->getProjectCount($item)),
        ];
    }

    private function getProjectCount($item): string
    {
        return (string) DB::table('projects')
            ->where('category_id', $item->id)
            ->count();
    }

    /**
     * @return list<FieldContract|ComponentContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                Text::make('Name', 'name')
                    ->required()
                    ->placeholder('Enter category name'),
                
                Slug::make('Slug', 'slug')
                    ->from('name'),
                
                Radio::make('Type', 'type')
                    ->options([
                        'house' => 'House',
                        'sauna' => 'Sauna',
                    ])
                    ->default('house'),
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
            Text::make('Type', 'type'),
        ];
    }

    protected function rules($item): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:project_categories,slug,' . ($item?->id ?? '')],
            'type' => ['required', 'in:house,sauna'],
        ];
    }
}