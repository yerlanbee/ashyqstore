<?php

namespace App\Infrastructure\Repositories\Contracts;

use Illuminate\Support\Collection;

interface TransactionRepositoryInterface
{
    /**
     * Получить обогащенные данные транзакций с учетом фильтров
     */
    public function getEnrichedTransactions(array $filters): Collection;

    /**
     * Получить общую сумму и количество из последнего ответа API
     */
    public function getSummary(): array;
}
