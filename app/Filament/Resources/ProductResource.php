<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Infrastructure\Models\Category;
use App\Infrastructure\Models\Fridge;
use App\Infrastructure\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $activeNavigationIcon = 'heroicon-s-cube';

    protected static ?string $navigationLabel = 'Продукты';

    protected static ?string $modelLabel = 'Продукт';

    protected static ?string $pluralModelLabel = 'Продукты';

    protected static ?string $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Категория' => $record->category?->name ?? '—',
            'Код' => $record->code ?? '—',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Основная информация')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Название')
                                    ->placeholder('Введите название продукта')
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-tag')
                                    ->columnSpan(2),

                                Forms\Components\Select::make('category_id')
                                    ->label('Категория')
                                    ->options(fn () => Category::query()->orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Business Cloud категории не отдаёт — проставляется вручную')
                                    ->prefixIcon('heroicon-o-rectangle-stack'),

                                Forms\Components\Select::make('fridge_id')
                                    ->label('Холодильник')
                                    ->options(fn () => Fridge::query()->orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->prefixIcon('heroicon-o-building-storefront'),

                                Forms\Components\TextInput::make('code')
                                    ->label('Код товара')
                                    ->placeholder('Код товара')
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-hashtag')
                                    ->columnSpan(2),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Склад и цена')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Количество')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required()
                                    ->suffix('шт.')
                                    ->prefixIcon('heroicon-o-cube'),

                                Forms\Components\TextInput::make('price')
                                    ->label('Цена')
                                    ->numeric()
                                    ->step('0.01')
                                    ->minValue(0)
                                    ->default(0)
                                    ->required()
                                    ->suffix('₸')
                                    ->prefixIcon('heroicon-o-banknotes'),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Медиа')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Forms\Components\TextInput::make('image')
                                    ->label('Изображение')
                                    ->placeholder('URL или имя файла')
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-photo'),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Видимость')
                            ->icon('heroicon-o-eye')
                            ->schema([
                                Forms\Components\Toggle::make('is_visible')
                                    ->label('Видимый в каталоге')
                                    ->default(true)
                                    ->onIcon('heroicon-m-eye')
                                    ->offIcon('heroicon-m-eye-slash')
                                    ->onColor('success')
                                    ->offColor('gray'),

                                Forms\Components\TextInput::make('sort')
                                    ->label('Сортировка')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->prefixIcon('heroicon-o-bars-arrow-down')
                                    ->helperText('Меньше — выше'),
                            ]),

                        Forms\Components\Section::make('Идентификаторы')
                            ->icon('heroicon-o-finger-print')
                            ->schema([
                                Forms\Components\TextInput::make('uuid')
                                    ->label('UUID')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(fn () => (string) Str::uuid())
                                    ->prefixIcon('heroicon-o-finger-print'),

                                Forms\Components\TextInput::make('external_id')
                                    ->label('ID в Business Cloud')
                                    ->helperText('Связывает товар с транзакциями. Заполняется импортом.')
                                    ->unique(ignoreRecord: true)
                                    ->prefixIcon('heroicon-o-link'),
                            ])
                            ->collapsible()
                            ->collapsed(fn (string $operation) => $operation === 'create'),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with(['category', 'fridge']))
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('')
                    ->square()
                    ->size(48)
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode(Str::limit($record->name ?? 'P', 2, '')) . '&background=f97316&color=fff'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Product $record): ?string => $record->uuid ? Str::limit($record->uuid, 16) : null),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Категория')
                    ->placeholder('—')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-rectangle-stack')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fridge.name')
                    ->label('Холодильник')
                    ->placeholder('—')
                    ->icon('heroicon-o-building-storefront')
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Код товара')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->fontFamily('mono')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('external_id')
                    ->label('BC')
                    ->tooltip(fn (Product $record) => $record->external_id ?? 'Не связан с Business Cloud — продажи по этому товару не отобразятся')
                    ->icon(fn ($state) => $state ? 'heroicon-m-link' : 'heroicon-m-link-slash')
                    ->color(fn ($state) => $state ? 'success' : 'warning')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('На складе')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state <= 0 => 'danger',
                        $state < 5 => 'warning',
                        default => 'success',
                    })
                    ->icon(fn ($state) => match (true) {
                        $state <= 0 => 'heroicon-m-exclamation-triangle',
                        $state < 5 => 'heroicon-m-exclamation-circle',
                        default => 'heroicon-m-check-circle',
                    })
                    ->formatStateUsing(fn ($state) => $state . ' шт.')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ' ') . ' ₸')
                    ->sortable()
                    ->alignEnd()
                    ->weight('medium'),

                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Видим.')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('fridge_id')
                    ->label('Холодильник')
                    ->relationship('fridge', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label('Видимость'),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Мало на складе')
                    ->query(fn ($query) => $query->where('quantity', '<', 5))
                    ->toggle(),

                Tables\Filters\Filter::make('unlinked')
                    ->label('Без связи с Business Cloud')
                    ->query(fn ($query) => $query->whereNull('external_id'))
                    ->toggle(),

                Tables\Filters\Filter::make('no_category')
                    ->label('Без категории')
                    ->query(fn ($query) => $query->whereNull('category_id'))
                    ->toggle(),
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
            ->emptyStateIcon('heroicon-o-cube')
            ->emptyStateHeading('Продуктов пока нет')
            ->emptyStateDescription('Добавьте первый товар в каталог')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->label('Создать продукт'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
