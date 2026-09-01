<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CameraResource\Pages;
use App\Infrastructure\Models\Camera;
use App\Infrastructure\Models\Fridge;
use App\Infrastructure\Services\Contracts\EzvizServiceContract;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Throwable;

class CameraResource extends Resource
{
    protected static ?string $model = Camera::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    protected static ?string $activeNavigationIcon = 'heroicon-s-video-camera';

    protected static ?string $navigationLabel = 'Камеры';

    protected static ?string $modelLabel = 'Камера';

    protected static ?string $pluralModelLabel = 'Камеры';

    protected static ?string $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 15;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        $active = static::getModel()::query()->where('is_active', true)->count();
        $total = static::getModel()::query()->count();

        return "{$active}/{$total}";
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Камера')
                    ->description('Подключение Ezviz по серийному номеру и коду верификации с наклейки')
                    ->icon('heroicon-o-video-camera')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->placeholder('Например: Микромаркет №1, вход')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-tag')
                            ->columnSpan(2),

                        Forms\Components\Select::make('fridge_id')
                            ->label('Холодильник')
                            ->options(fn () => Fridge::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->prefixIcon('heroicon-o-building-storefront')
                            ->helperText('Опционально — привязка к микромаркету'),

                        Forms\Components\TextInput::make('serial')
                            ->label('Серийный номер')
                            ->placeholder('Напр.: AA1234567')
                            ->required()
                            ->maxLength(64)
                            ->unique(ignoreRecord: true)
                            ->prefixIcon('heroicon-o-finger-print')
                            ->helperText('С наклейки на устройстве'),

                        Forms\Components\TextInput::make('verification_code')
                            ->label('Код верификации')
                            ->placeholder('Оставьте пустым, если шифрование отключено')
                            ->maxLength(64)
                            ->password()
                            ->revealable()
                            ->prefixIcon('heroicon-o-key')
                            ->helperText('Нужен только если в приложении EZVIZ включён Video Encryption'),

                        Forms\Components\TextInput::make('channel_no')
                            ->label('Номер канала')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(64)
                            ->default(1)
                            ->required()
                            ->prefixIcon('heroicon-o-hashtag'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true)
                            ->onIcon('heroicon-m-bolt')
                            ->offIcon('heroicon-m-bolt-slash')
                            ->onColor('success')
                            ->offColor('gray'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('fridge'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-video-camera')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('fridge.name')
                    ->label('Холодильник')
                    ->placeholder('—')
                    ->icon('heroicon-o-building-storefront')
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('serial')
                    ->label('Serial')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('channel_no')
                    ->label('Канал')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Статус')
                    ->boolean()
                    ->trueIcon('heroicon-o-bolt')
                    ->falseIcon('heroicon-o-bolt-slash')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('fridge_id')
                    ->label('Холодильник')
                    ->relationship('fridge', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активность'),
            ])
            ->actions([
                Tables\Actions\Action::make('live')
                    ->label('Смотреть')
                    ->icon('heroicon-m-play')
                    ->color('primary')
                    ->url(fn (Camera $record) => Pages\ViewLive::getUrl(['record' => $record->getKey()]))
                    ->visible(fn (Camera $record) => $record->is_active),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('checkEncryption')
                        ->label('Проверить шифрование')
                        ->icon('heroicon-m-lock-closed')
                        ->color('gray')
                        ->action(function (Camera $record) {
                            try {
                                $enabled = app(EzvizServiceContract::class)->isEncryptionEnabled($record->serial);

                                Notification::make()
                                    ->title($enabled ? 'Шифрование включено' : 'Шифрование выключено')
                                    ->body($enabled
                                        ? "Камере {$record->serial} требуется код верификации."
                                        : "Камера {$record->serial} отдаёт поток без шифрования — поле кода можно оставить пустым.")
                                    ->color($enabled ? 'warning' : 'success')
                                    ->icon($enabled ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                                    ->send();
                            } catch (Throwable $e) {
                                Notification::make()
                                    ->title('Не удалось проверить шифрование')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Tables\Actions\EditAction::make()->icon('heroicon-m-pencil-square'),
                    Tables\Actions\DeleteAction::make()->icon('heroicon-m-trash'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-video-camera')
            ->emptyStateHeading('Камер пока нет')
            ->emptyStateDescription('Добавьте первую камеру Ezviz по серийному номеру')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->label('Добавить'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCameras::route('/'),
            'create' => Pages\CreateCamera::route('/create'),
            'edit' => Pages\EditCamera::route('/{record}/edit'),
            'live' => Pages\ViewLive::route('/{record}/live'),
        ];
    }
}
