<?php

namespace App\Orchid\Filters\Transaction;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Select;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function display(): iterable
    {
        return [
            Select::make('pageSize')
                ->options([
                    20 => '20',
                    50 => '50',
                    100 => '100',
                    150 => '150',
                    200 => '200',
                ])
                ->empty()
                ->value($this->request->get('pageSize', 50))
                ->title($this->name()),
        ];
    }

    public function value(): string
    {
        return $this->name() . ': ' . $this->request->get('pageSize', 10);
    }
}
