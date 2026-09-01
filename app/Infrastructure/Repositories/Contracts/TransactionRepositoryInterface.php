<?php

namespace App\Infrastructure\Repositories\Contracts;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

interface TransactionRepositoryInterface
{
    /**
     * Все транзакции за период, со сквозным обходом страниц.
     *
     * API отдаёт максимум 100 записей за раз, поэтому и для таблицы,
     * и для аналитики нужен обход всех страниц. Результат кэшируется.
     *
     * @param  string[]  $terminalIds  Пустой массив — все терминалы.
     * @param  int[]|null  $paymentMethods
     */
    public function getRawForPeriod(
        CarbonInterface $from,
        CarbonInterface $to,
        array $terminalIds = [],
        ?array $paymentMethods = null,
    ): Collection;

    /**
     * Свести транзакции в строки «товар × микромаркет».
     */
    public function groupByProduct(Collection $items): Collection;
}
