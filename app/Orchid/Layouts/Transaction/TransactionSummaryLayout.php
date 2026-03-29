<?php

namespace App\Orchid\Layouts\Transaction;

use Orchid\Screen\Layouts\Legend;
use Orchid\Screen\Sight;

class TransactionSummaryLayout extends Legend
{
    protected $target = 'summary';

    protected function columns(): iterable
    {
        return [
            Sight::make('totalAmount', 'Общая сумма продаж')
                ->render(fn ($summary) => number_format((float) ($summary['totalAmount'] ?? 0), 2, '.', ' ') . ' ₸'),

            Sight::make('totalCount', 'Количество продаж')
                ->render(fn ($summary) => (string) ($summary['totalCount'] ?? 0)),
        ];
    }
}
