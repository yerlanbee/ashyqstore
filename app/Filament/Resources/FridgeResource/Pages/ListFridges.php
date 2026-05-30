<?php

namespace App\Filament\Resources\FridgeResource\Pages;

use App\Filament\Resources\FridgeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFridges extends ListRecords
{
    protected static string $resource = FridgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Добавить'),
        ];
    }
}
