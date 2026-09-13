<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Infrastructure\Models\Category;
use App\Infrastructure\Models\Fridge;
use App\Infrastructure\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Добавление товаров';

    protected int $createdCount = 0;

    /** @var array<int, string>|null */
    protected ?array $categoryOptions = null;

    /** @var array<int, string>|null */
    protected ?array $fridgeOptions = null;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Repeater::make('items')
                    ->hiddenLabel()
                    ->addActionLabel('Добавить ещё товар')
                    ->defaultItems(1)
                    ->minItems(1)
                    ->cloneable()
                    ->reorderable(false)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->placeholder('Введите название продукта')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-tag')
                            ->columnSpan(['default' => 12, 'md' => 5]),

                        Forms\Components\TextInput::make('code')
                            ->label('Код товара')
                            ->placeholder('Код товара')
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-hashtag')
                            ->columnSpan(['default' => 12, 'md' => 4]),

                        Forms\Components\Toggle::make('is_visible')
                            ->label('Видимый')
                            ->default(true)
                            ->inline(false)
                            ->onIcon('heroicon-m-eye')
                            ->offIcon('heroicon-m-eye-slash')
                            ->onColor('success')
                            ->offColor('gray')
                            ->columnSpan(['default' => 12, 'md' => 3]),

                        Forms\Components\Select::make('category_id')
                            ->label('Категория')
                            ->options(fn (): array => $this->getCategoryOptions())
                            ->searchable()
                            ->prefixIcon('heroicon-o-rectangle-stack')
                            ->columnSpan(['default' => 12, 'md' => 4]),

                        Forms\Components\Select::make('fridge_ids')
                            ->label('Холодильники')
                            ->options(fn (): array => $this->getFridgeOptions())
                            ->default(fn (): array => array_keys($this->getFridgeOptions()))
                            ->multiple()
                            ->searchable()
                            ->prefixIcon('heroicon-o-building-storefront')
                            ->helperText('Для каждого выбранного холодильника создаётся отдельная карточка товара')
                            ->columnSpan(['default' => 12, 'md' => 8]),

                        Forms\Components\TextInput::make('price')
                            ->label('Цена')
                            ->numeric()
                            ->step('0.01')
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->suffix('₸')
                            ->columnSpan(['default' => 6, 'md' => 2]),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->suffix('шт.')
                            ->helperText('В каждом холодильнике')
                            ->columnSpan(['default' => 6, 'md' => 2]),

                        Forms\Components\TextInput::make('image')
                            ->label('Изображение')
                            ->placeholder('URL или имя файла')
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-photo')
                            ->columnSpan(['default' => 12, 'md' => 8]),
                    ])
                    ->columns(12),
            ])
            ->columns(1);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $products = collect($data['items'])->flatMap(function (array $item): array {
            $fridgeIds = $item['fridge_ids'] ?: [null];
            unset($item['fridge_ids']);

            return array_map(
                fn ($fridgeId): Product => Product::create([
                    ...$item,
                    'fridge_id' => $fridgeId === null ? null : (int) $fridgeId,
                    'uuid' => (string) Str::uuid(),
                ]),
                $fridgeIds,
            );
        });

        $this->createdCount = $products->count();

        return $products->first();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return $this->createdCount > 1
            ? 'Добавлено товаров: '.$this->createdCount
            : 'Товар добавлен';
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    /**
     * @return array<int, string>
     */
    protected function getCategoryOptions(): array
    {
        return $this->categoryOptions ??= Category::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<int, string>
     */
    protected function getFridgeOptions(): array
    {
        return $this->fridgeOptions ??= Fridge::query()->orderBy('name')->pluck('name', 'id')->all();
    }
}
