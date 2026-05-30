<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Infrastructure\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $activeNavigationIcon = 'heroicon-s-rectangle-stack';

    protected static ?string $navigationLabel = 'Категории';

    protected static ?string $modelLabel = 'Категория';

    protected static ?string $pluralModelLabel = 'Категории';

    protected static ?string $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 20;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->description('Название и видимость категории в каталоге')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->placeholder('Введите название категории')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2)
                            ->prefixIcon('heroicon-o-tag'),

                        Forms\Components\TextInput::make('sort')
                            ->label('Сортировка')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->prefixIcon('heroicon-o-bars-arrow-down')
                            ->helperText('Чем меньше — тем выше в списке'),

                        Forms\Components\Toggle::make('is_visible')
                            ->label('Видимый')
                            ->default(true)
                            ->onIcon('heroicon-m-eye')
                            ->offIcon('heroicon-m-eye-slash')
                            ->onColor('success')
                            ->offColor('gray'),
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

                Forms\Components\Section::make('Идентификаторы')
                    ->icon('heroicon-o-finger-print')
                    ->schema([
                        Forms\Components\TextInput::make('uuid')
                            ->label('UUID')
                            ->placeholder('Сгенерируется автоматически')
                            ->default(fn () => (string) Str::uuid())
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->prefixIcon('heroicon-o-finger-print'),
                    ])
                    ->collapsible()
                    ->collapsed(fn (string $operation) => $operation === 'create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->modifyQueryUsing(fn ($query) => $query->withCount('products'))
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=' . urlencode('K') . '&background=f97316&color=fff'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-tag'),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Продуктов')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                    ->icon('heroicon-o-cube')
                    ->sortable(),

                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('UUID скопирован')
                    ->limit(12)
                    ->fontFamily('mono')
                    ->color('gray')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Видимый')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\TextColumn::make('sort')
                    ->label('Сорт.')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label('Видимость'),
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
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->emptyStateHeading('Категорий пока нет')
            ->emptyStateDescription('Создайте первую категорию для каталога')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->label('Создать категорию'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
