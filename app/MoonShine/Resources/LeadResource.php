<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Lead;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Decorations\Divider;
use MoonShine\UI\Decorations\Tab;
use MoonShine\UI\Decorations\Tabs;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\DateRange;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;

#[Icon('heroicons.inbox-arrow-down')]
class LeadResource extends ModelResource
{
    protected string $model = Lead::class;

    protected string $column = 'created_at';

    public function getIcon(): string
    {
        return 'heroicons.inbox-arrow-down';
    }

    public function getBadge($item): string
    {
        $statusColors = [
            'new' => 'red',
            'processed' => 'yellow',
            'completed' => 'green',
        ];
        
        $color = $statusColors[$item->status] ?? 'gray';
        
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $color,
            ucfirst($item->status)
        );
    }

    /**
     * @return list<FieldContract|ComponentContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Name', 'name'),
            Text::make('Phone', 'phone'),
            Select::make('Status', 'status')
                ->options([
                    'new' => 'New',
                    'processed' => 'Processed',
                    'completed' => 'Completed',
                ])
                ->badge(fn($item) => $this->getStatusColor($item->status)),
            Text::make('Created At', 'created_at')
                ->sortable()
                ->formatState(fn($state) => $state->format('Y-m-d H:i')),
        ];
    }

    private function getStatusColor(string $status): string
    {
        return match($status) {
            'new' => 'red',
            'processed' => 'yellow',
            'completed' => 'green',
            default => 'gray',
        };
    }

    /**
     * @return list<FieldContract|ComponentContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                Tabs::make([
                    Tab::make('Contact Info', [
                        Text::make('Name', 'name')
                            ->readonly(),
                        
                        Text::make('Phone', 'phone')
                            ->readonly(),
                        
                        Text::make('Email', 'email')
                            ->readonly(),
                        
                        Text::make('Source', 'source')
                            ->readonly(),
                    ]),
                    
                    Tab::make('Message', [
                        Textarea::make('Message', 'message')
                            ->rows(6)
                            ->readonly(),
                    ]),
                    
                    Tab::make('Status', [
                        Select::make('Status', 'status')
                            ->options([
                                'new' => 'New',
                                'processed' => 'Processed',
                                'completed' => 'Completed',
                            ])
                            ->required(),
                        
                        Textarea::make('Notes', 'notes')
                            ->rows(4)
                            ->placeholder('Internal notes'),
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
            Select::make('Status', 'status')
                ->options([
                    'new' => 'New',
                    'processed' => 'Processed',
                    'completed' => 'Completed',
                ]),
            
            DateRange::make('Created At', 'created_at'),
            
            Text::make('Source', 'source'),
        ];
    }

    protected function rules($item): array
    {
        return [
            'status' => ['required', 'in:new,processed,completed'],
        ];
    }

    public function actions(): array
    {
        return [
            ActionButton::make('Mark as Processed', function(Lead $item) {
                $item->update(['status' => 'processed']);
                return 'Status changed to Processed';
            })
                ->canSee(fn($item) => $item->status === 'new')
                ->primary(),
            
            ActionButton::make('Mark as Completed', function(Lead $item) {
                $item->update(['status' => 'completed']);
                return 'Status changed to Completed';
            })
                ->canSee(fn($item) => in_array($item->status, ['new', 'processed']))
                ->success(),
            
            ActionButton::make('Reset to New', function(Lead $item) {
                $item->update(['status' => 'new']);
                return 'Status changed to New';
            })
                ->canSee(fn($item) => $item->status !== 'new')
                ->warning(),
        ];
    }
}