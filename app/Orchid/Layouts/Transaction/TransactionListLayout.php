<?php

namespace App\Orchid\Layouts\Transaction;

use Carbon\Carbon;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class TransactionListLayout extends Table
{
    protected $target = 'transactions';

    protected function columns(): iterable
    {
        return [
            TD::make('name', 'Название транзакции')
                ->sort()
                ->render(fn (array $transaction) => $transaction['name'] ?? ''),

            TD::make('amount', 'Сумма оплаты')
                ->render(function (array $transaction) {
                    return number_format((float) ($transaction['amount'] ?? 0), 2, '.', ' ');
                }),

            TD::make('count', 'Количество')
                ->align(TD::ALIGN_CENTER)
                ->render(fn (array $transaction) => $transaction['count'] ?? 0),

            TD::make('paid_at', 'Время')
                ->render(function (array $transaction) {
                    $value = $transaction['paid_at'] ?? null;

                    if (!$value) {
                        return '-';
                    }

                    try {
                        return Carbon::parse($value)->format('d.m.Y H:i:s');
                    } catch (\Throwable) {
                        return $value;
                    }
                }),
        ];
    }
}
