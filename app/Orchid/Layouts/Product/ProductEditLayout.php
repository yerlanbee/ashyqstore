<?php

namespace App\Orchid\Layouts\Product;

use App\Infrastructure\Models\Category;
use App\Infrastructure\Models\Fridge;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Layouts\Rows;

class ProductEditLayout extends Rows
{
    protected function fields(): iterable
    {
        return [
            Input::make('product.name')
                ->title('Название')
                ->required()
                ->placeholder('Введите название продукта'),

            Relation::make('product.category_id')
                ->title('Категория')
                ->fromModel(Category::class, 'name')
                ->required(),

            Relation::make('product.fridge_id')
                ->title('Холодильник')
                ->fromModel(Fridge::class, 'name')
                ->required(),

            Input::make('product.code')
                ->title('Код')
                ->placeholder('Введите код товара'),

            Input::make('product.quantity')
                ->title('Количество')
                ->type('number')
                ->required()
                ->value(0),

            Input::make('product.price')
                ->title('Цена')
                ->type('number')
                ->step('0.01')
                ->required()
                ->value(0),

            Input::make('product.sort')
                ->title('Сортировка')
                ->type('number')
                ->value(0),

            Picture::make('product.image')
                ->title('Изображение')
                ->targetId(),

            Input::make('product.uuid')
                ->title('UUID')
                ->readonly(),
        ];
    }
}
