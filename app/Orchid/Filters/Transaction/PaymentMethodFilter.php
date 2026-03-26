<?php

namespace App\Orchid\Filters\Transaction;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Select;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PaymentMethodFilter extends Filter
{
    private const METHODS = [
        null => 'Все',
        1 => 'Kaspi',
        2 => 'Halyk',
    ];

    public function name(): string
    {
        return 'Методы оплаты';
    }

    public function parameters(): array
    {
        return ['paymentMethods'];
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
            Select::make('paymentMethods.')
                ->multiple()
                ->empty()
                ->options(self::METHODS)
                ->value((array) $this->request->get('paymentMethods', []))
                ->title($this->name()),
        ];
    }

    public function value(): string
    {
        $methods = (array) $this->request->get('paymentMethods', []);

        if (empty($methods)) {
            return '';
        }

        $labels = array_map(
            fn (string $method) => self::METHODS[$method] ?? $method,
            $methods
        );

        return $this->name() . ': ' . implode(', ', $labels);
    }
}
