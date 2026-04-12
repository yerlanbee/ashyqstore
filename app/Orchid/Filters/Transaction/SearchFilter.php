<?php

namespace App\Orchid\Filters\Transaction;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Input;

class SearchFilter extends Filter
{
    /**
     * Параметр в URL, например: ?search=молоко
     */
    public function parameters(): array
    {
        return ['search'];
    }

    /**
     * Метод run() здесь не будет делать ничего с Query Builder,
     * так как мы фильтруем Коллекцию вручную в Screen.
     */
    public function run(Builder $builder): Builder
    {
        return $builder;
    }

    /**
     * Отображение поля в интерфейсе
     */
    public function display(): iterable
    {
        return [
            Input::make('search')
                ->type('text')
                ->value($this->request->get('search'))
                ->placeholder('Название или код...')
                ->title('Поиск товара'),
        ];
    }
}
