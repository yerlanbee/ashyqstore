<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Infrastructure\Models\Fridge;
use App\Infrastructure\Services\SalesAnalyticsService;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class SalesAnalytics extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $activeNavigationIcon = 'heroicon-s-chart-bar-square';

    protected static ?string $navigationLabel = 'Аналитика продаж';

    protected static ?string $title = 'Аналитика продаж';

    protected static ?string $navigationGroup = 'Аналитика';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.sales-analytics';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'days' => 7,
            'terminalIds' => [],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('days')
                    ->label('Период')
                    ->options([
                        7 => 'Последние 7 дней',
                        14 => 'Последние 14 дней',
                        30 => 'Последние 30 дней',
                    ])
                    ->default(7)
                    ->selectablePlaceholder(false)
                    ->live(),

                Select::make('terminalIds')
                    ->label('Микромаркеты')
                    ->placeholder('Все микромаркеты')
                    ->multiple()
                    ->options(fn () => Fridge::query()->orderBy('name')->pluck('name', 'uuid'))
                    ->searchable()
                    ->live(),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function getViewData(): array
    {
        return app(SalesAnalyticsService::class)->overview(
            terminalIds: array_values(array_filter((array) ($this->data['terminalIds'] ?? []))),
            days: (int) ($this->data['days'] ?? 7),
        );
    }
}
