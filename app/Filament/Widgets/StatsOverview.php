<?php

namespace App\Filament\Widgets;

use App\Infrastructure\Models\Category;
use App\Infrastructure\Models\Fridge;
use App\Infrastructure\Models\Product;
use App\Infrastructure\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $lowStock = Product::query()->where('quantity', '<', 5)->count();
        $activeFridges = Fridge::query()->where('is_active', true)->count();
        $totalFridges = Fridge::query()->count();

        return [
            Stat::make('Микромаркеты', "{$activeFridges} / {$totalFridges}")
                ->description($activeFridges === $totalFridges ? 'Все активны' : 'Активных терминалов')
                ->descriptionIcon($activeFridges === $totalFridges ? 'heroicon-m-check-circle' : 'heroicon-m-bolt')
                ->color($activeFridges === $totalFridges ? 'success' : 'warning')
                ->icon('heroicon-o-building-storefront')
                ->chart([7, 8, $activeFridges, $totalFridges, $activeFridges, $totalFridges, $activeFridges]),

            Stat::make('Продукты', (string) Product::query()->count())
                ->description($lowStock > 0 ? "{$lowStock} с низким остатком" : 'Остатки в норме')
                ->descriptionIcon($lowStock > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($lowStock > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-cube'),

            Stat::make('Категории', (string) Category::query()->count())
                ->description('Разделы каталога')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('info')
                ->icon('heroicon-o-rectangle-stack'),

            Stat::make('Пользователи', (string) User::query()->count())
                ->description('Имеют доступ к панели')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->icon('heroicon-o-users'),
        ];
    }
}
