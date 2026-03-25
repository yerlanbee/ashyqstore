<?php

namespace App\Orchid\Layouts\Product;

use App\Infrastructure\Models\Fridge;
use App\Infrastructure\Models\Product;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ProductListLayout extends Table
{
    protected $target = 'products';

    protected function columns(): array
    {
        return [
            TD::make('name', 'Название')
                ->sort()
                ->cantHide()
                ->filter(Input::make())
                ->render(fn (Product $fridge) => $fridge->name),

            TD::make('uuid', __('Уникальный номер'))
                ->sort()
                ->cantHide()
                ->filter(Input::make())
                ->render(fn (Product $product) => $product->uuid),

            TD::make('category', 'Категория')
                ->render(fn (Product $product) => $product->category?->name ?? '—'),

            TD::make('fridge', 'Холодильник')
                ->render(fn (Product $product) => $product->fridge?->name ?? '—'),

            TD::make('code', 'Код')
                ->render(fn (Product $product) => $product->code ?? '—'),

            TD::make('quantity', 'Количество')
                ->render(fn (Product $product) => $product->quantity ?? 0),

            TD::make(__('Действия'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (Product $product) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make(__('Отредактировать'))
                            ->route('platform.systems.products.edit', $product->id)
                            ->icon('bs.pencil'),

                        Button::make(__('Удалить'))
                            ->icon('bs.trash3')
                            ->confirm(__('Вы действительно хотите удалить?'))
                            ->method('remove', [
                                'id' => $product->id,
                            ]),
                    ])),
        ];
    }
}
