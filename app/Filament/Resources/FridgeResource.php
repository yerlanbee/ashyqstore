<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FridgeResource\Pages;
use App\Infrastructure\Models\Fridge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FridgeResource extends Resource
{
    protected static ?string $model = Fridge::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $activeNavigationIcon = 'heroicon-s-building-storefront';

    protected static ?string $navigationLabel = 'Микромаркеты';

    protected static ?string $modelLabel = 'Микромаркет';

    protected static ?string $pluralModelLabel = 'Микромаркеты';

    protected static ?string $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 10;

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
                Forms\Components\Section::make('Микромаркет')
                    ->description('Терминал, привязанный к холодильнику')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->placeholder('Название')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-tag')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('uuid')
                            ->label('Уникальный номер')
                            ->placeholder('Уникальный номер')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-finger-print')
                            ->columnSpan(2),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Активен')
                            ->default(false)
                            ->onIcon('heroicon-m-bolt')
                            ->offIcon('heroicon-m-bolt-slash')
                            ->onColor('success')
                            ->offColor('gray')
                            ->columnSpan(2),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->withCount('products'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-storefront')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('uuid')
                    ->label('Уникальный номер')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('UUID скопирован')
                    ->fontFamily('mono')
                    ->color('gray')
                    ->limit(18),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Товары')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'info' : 'gray')
                    ->icon('heroicon-o-cube'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Статус')
                    ->boolean()
                    ->trueIcon('heroicon-o-bolt')
                    ->falseIcon('heroicon-o-bolt-slash')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активность'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()->icon('heroicon-m-pencil-square'),
                    Tables\Actions\DeleteAction::make()->icon('heroicon-m-trash'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-building-storefront')
            ->emptyStateHeading('Микромаркетов пока нет')
            ->emptyStateDescription('Добавьте первый холодильник')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->label('Добавить'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFridges::route('/'),
            'create' => Pages\CreateFridge::route('/create'),
            'edit' => Pages\EditFridge::route('/{record}/edit'),
        ];
    }
}
