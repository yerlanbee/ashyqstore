<?php

namespace App\Orchid\Layouts\Transaction;

use App\Orchid\Filters\Transaction\DateTimeFromFilter;
use App\Orchid\Filters\Transaction\DateTimeToFilter;
use App\Orchid\Filters\Transaction\PageSizeFilter;
use App\Orchid\Filters\Transaction\PaymentMethodFilter;
use App\Orchid\Filters\Transaction\SearchFilter;
use App\Orchid\Filters\Transaction\TerminalFilter;
use Orchid\Filters\Filter;
use Orchid\Screen\Layouts\Selection;

class TransactionFilterLayout extends Selection
{
    /**
     * @return string[]|Filter[]
     */
    public function filters(): array
    {
        return [
            SearchFilter::class,
            PageSizeFilter::class,
            DateTimeFromFilter::class,
            DateTimeToFilter::class,
            TerminalFilter::class,
            PaymentMethodFilter::class,
        ];
    }
}
