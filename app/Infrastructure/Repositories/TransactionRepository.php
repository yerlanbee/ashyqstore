<?php
namespace App\Infrastructure\Repositories;

use App\Infrastructure\Models\Fridge;
use App\Infrastructure\Services\BusinessCloudService;
use App\Infrastructure\Services\Contracts\BusinessClodServiceContract;
use App\Infrastructure\Services\ProductResolver;
use App\Infrastructure\Repositories\Contracts\TransactionRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TransactionRepository implements TransactionRepositoryInterface
{
    private const CACHE_TTL = 300;

    /** Предохранитель от бесконечного обхода, если API отдаёт кривой totalCount. */
    private const MAX_PAGES = 100;

    public function __construct(
        protected BusinessClodServiceContract $apiService,
        protected ProductResolver $products,
    ) {}

    public function getRawForPeriod(
        CarbonInterface $from,
        CarbonInterface $to,
        array $terminalIds = [],
        ?array $paymentMethods = null,
    ): Collection {
        sort($terminalIds);

        $key = 'business_cloud:tx:' . md5(implode('|', [
            $from->toIso8601ZuluString(),
            $to->toIso8601ZuluString(),
            implode(',', $terminalIds),
            implode(',', $paymentMethods ?? []),
        ]));

        return Cache::remember($key, self::CACHE_TTL, function () use ($from, $to, $terminalIds, $paymentMethods) {
            $items = collect();
            $page = 1;

            do {
                $response = $this->apiService->getTransactions([
                    'page' => $page,
                    'pageSize' => BusinessCloudService::MAX_PAGE_SIZE,
                    'dateTimeFrom' => $from->clone()->utc()->format('Y-m-d\TH:i:s.v\Z'),
                    'dateTimeTo' => $to->clone()->utc()->format('Y-m-d\TH:i:s.v\Z'),
                    // Пустой массив API трактует как «ни одного терминала»
                    // и возвращает ноль записей. Для «всех» нужен null.
                    'terminalIds' => $terminalIds ?: null,
                    'paymentMethods' => $paymentMethods ?: null,
                ]);

                $batch = $response['items'] ?? [];
                $items = $items->concat($batch);

                $hasMore = count($batch) === BusinessCloudService::MAX_PAGE_SIZE
                    && $items->count() < (int) ($response['totalCount'] ?? 0);

                $page++;
            } while ($hasMore && $page <= self::MAX_PAGES);

            return $items;
        });
    }

    /**
     * Ключ группировки — цена внутри микромаркета: один и тот же товар
     * в разных холодильниках должен остаться отдельной строкой.
     *
     * По productId группировать нельзя: в Business Cloud товар заведён
     * отдельной позицией на каждую полку, поэтому у него несколько id,
     * и количество дробится на несколько строк вместо одной.
     *
     * Название и категорию ProductResolver берёт из нашего каталога по цене.
     */
    public function groupByProduct(Collection $items): Collection
    {
        if ($items->isEmpty()) {
            return collect();
        }

        // Сопоставляем по terminalId, а не по имени: code у холодильника
        // заполнен не всегда, а uuid приходит в каждой транзакции.
        $fridges = Fridge::whereIn('uuid', $items->pluck('terminalId')->filter()->unique())
            ->get()
            ->keyBy('uuid');

        return $items
            ->groupBy(fn (array $item) => implode('|', [
                $item['terminalId'] ?? '',
                number_format((float) ($item['amount'] ?? 0), 2, '.', ''),
            ]))
            ->map(function (Collection $group) use ($fridges) {
                $first = $group->first();
                $fridge = $fridges->get($first['terminalId'] ?? null);
                $amount = (float) ($first['amount'] ?? 0);

                $product = $this->products->resolve(
                    $first['terminalId'] ?? null,
                    $amount,
                    $first['description'] ?? null,
                );

                return [
                    'name'          => $fridge?->name ?? $first['terminalName'] ?? 'Не определено',
                    'amount'        => $amount,
                    'total'         => (float) $group->sum('amount'),
                    'product_code'  => $product['shelf'],
                    'product_name'  => $product['name'],
                    'category'      => $product['category'],
                    'count'         => $group->count(),
                    'paid_at'       => $group->max('transactionDate'),
                ];
            })
            ->values();
    }
}
