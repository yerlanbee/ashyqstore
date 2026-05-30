<?php

namespace App\Filament\Resources\FridgeResource\Pages;

use App\Filament\Resources\FridgeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFridge extends EditRecord
{
    protected static string $resource = FridgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
