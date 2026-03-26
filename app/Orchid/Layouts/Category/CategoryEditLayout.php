<?php

namespace App\Orchid\Layouts\Category;

use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CategoryEditLayout extends Rows
{
    protected function fields(): iterable
    {
        return [
            Input::make('category.name')
                ->title('Название')
                ->placeholder('Введите название категории')
                ->required(),

            Input::make('category.uuid')
                ->title('UUID')
                ->placeholder('Введите uuid')
                ->readonly(),

            Input::make('category.sort')
                ->title('Сортировка')
                ->type('number')
                ->value(0)
                ->required(),

            Input::make('category.image')
                ->title('Изображение')
                ->placeholder('URL или имя файла'),

            CheckBox::make('category.is_visible')
                ->title('Видимый')
                ->sendTrueOrFalse(),
        ];
    }
}
