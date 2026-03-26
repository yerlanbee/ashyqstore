<?php

namespace App\Orchid\Filters\Transaction;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Select;

class PageSizeFilter extends Filter
{
    public function name(): string
    {
        return 'Размер страницы';
    }

    public function parameters(): array
    {
        return ['pageSize'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder;
    }

    public function display(): iterable
    {
        return [
            Select::make('pageSize')
                ->options([
                    10 => '10',
                    25 => '25',
                    50 => '50',
                    100 => '100',
                ])
                ->empty()
                ->value($this->request->get('pageSize', 10))
                ->title($this->name()),
        ];
    }

    public function value(): string
    {
        return $this->name() . ': ' . $this->request->get('pageSize', 10);
    }
}
