<?php

namespace App\Filament\Resources\CameraResource\Pages;

use App\Filament\Resources\CameraResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCamera extends EditRecord
{
    protected static string $resource = CameraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('live')
                ->label('Смотреть')
                ->icon('heroicon-m-play')
                ->color('primary')
                ->url(fn () => ViewLive::getUrl(['record' => $this->record]))
                ->visible(fn () => $this->record->is_active),
            Actions\DeleteAction::make(),
        ];
    }
}
