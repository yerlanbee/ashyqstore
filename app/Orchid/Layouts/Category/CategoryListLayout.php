<?php

namespace App\Orchid\Layouts\Category;

use App\Infrastructure\Models\Category;
use App\Infrastructure\Models\Product;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class CategoryListLayout extends Table
{
    protected $target = 'categories';

    protected function columns(): iterable
    {
        return [
            TD::make('name', 'Название')
                ->sort()
                ->cantHide()
                ->filter(Input::make())
                ->render(fn (Category $category) => $category->name),

            TD::make('uuid', __('Уникальный номер'))
                ->sort()
                ->cantHide()
                ->filter(Input::make())
                ->render(fn (Category $category) => $category->uuid),

            TD::make(__('Действия'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (Category $category) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make(__('Отредактировать'))
                            ->route('platform.systems.categories.edit', $category->id)
                            ->icon('bs.pencil'),

                        Button::make(__('Удалить'))
                            ->icon('bs.trash3')
                            ->confirm(__('Вы действительно хотите удалить?'))
                            ->method('remove', [
                                'id' => $category->id,
                            ]),
                    ])),
        ];
    }
}
