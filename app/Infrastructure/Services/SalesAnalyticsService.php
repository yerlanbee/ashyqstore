<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Infrastructure\Models\Fridge;
use App\Infrastructure\Repositories\Contracts\TransactionRepositoryInterface;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class SalesAnalyticsService
{
    public function __construct(
        private readonly TransactionRepositoryInterface $transactions,
        private readonly ProductResolver $products,
    ) {}

    /**
     * Сводка продаж: сегодня против вчера, динамика за период и топ товаров.
     *
     * Все срезы считаются из одной выборки, чтобы не дёргать API по разу
     * на каждый блок — она и так обходит страницы по 100 записей.
     *
     * @param  string[]  $terminalIds
     */
    public function overview(array $terminalIds = [], int $days = 7, int $topLimit = 10): array
    {
        $today = $this->today();
        $from = $today->clone()->subDays($days - 1)->startOfDay();

        $sales = $this->transactions->getRawForPeriod($from, $today->clone()->endOfDay(), $terminalIds);

        $byDay = $sales->groupBy(fn (array $t) => $this->localDate($t)->toDateString());

        $todaySales = $byDay->get($today->toDateString(), collect());
        $yesterday = $today->clone()->subDay();
        $yesterdaySales = $byDay->get($yesterday->toDateString(), collect());

        // Вчера целиком сравнивать с неполным сегодня некорректно,
        // поэтому для дельты обрезаем вчерашний день по текущему времени.
        $cutoff = $this->now();
        $yesterdaySoFar = $yesterdaySales->filter(
            fn (array $t) => $this->localDate($t)->format('H:i:s') <= $cutoff->format('H:i:s')
        );

        return [
            'today' => $this->totals($todaySales),
            'yesterday' => $this->totals($yesterdaySales),
            'yesterday_so_far' => $this->totals($yesterdaySoFar),
            'delta' => [
                'units' => $this->delta($todaySales->count(), $yesterdaySoFar->count()),
                'revenue' => $this->delta((float) $todaySales->sum('amount'), (float) $yesterdaySoFar->sum('amount')),
            ],
            'period' => $this->totals($sales) + ['days' => $days],
            'daily' => $this->daily($byDay, $from, $today),
            'top' => $this->top($sales, $topLimit),
            'generated_at' => $this->now(),
        ];
    }

    /** @return array{units: int, revenue: float, avg_check: float} */
    private function totals(Collection $sales): array
    {
        $units = $sales->count();
        $revenue = (float) $sales->sum('amount');

        return [
            'units' => $units,
            'revenue' => $revenue,
            'avg_check' => $units > 0 ? round($revenue / $units, 2) : 0.0,
        ];
    }

    /** Процент изменения. null — когда база нулевая и сравнивать не с чем. */
    private function delta(float $current, float $previous): ?float
    {
        if ($previous <= 0.0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Ряд по дням без пропусков: дни без продаж должны быть видны как нули,
     * иначе график врёт о динамике.
     */
    private function daily(Collection $byDay, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return collect(Carbon::parse($from)->daysUntil($to->clone()->endOfDay()))
            ->map(function (Carbon $day) use ($byDay) {
                $sales = $byDay->get($day->toDateString(), collect());

                return [
                    'date' => $day->toDateString(),
                    'label' => $day->format('d.m'),
                    'weekday' => $day->locale('ru')->isoFormat('dd'),
                    'units' => $sales->count(),
                    'revenue' => (float) $sales->sum('amount'),
                ];
            });
    }

    /**
     * Строка топа — товар внутри микромаркета, как в таблице транзакций.
     *
     * Микромаркеты не смешиваем: одна цена в разных холодильниках может
     * принадлежать разным товарам (589 — и Gorilla, и Kinder), а каталог
     * у каждого свой.
     *
     * @return Collection<int, array>
     */
    private function top(Collection $sales, int $limit): Collection
    {
        $fridges = Fridge::query()->pluck('name', 'uuid');

        return $sales
            ->groupBy(fn (array $t) => implode('|', [
                $t['terminalId'] ?? '',
                number_format((float) ($t['amount'] ?? 0), 2, '.', ''),
            ]))
            ->map(function (Collection $group) use ($fridges) {
                $first = $group->first();

                return $this->products->resolve(
                    $first['terminalId'] ?? null,
                    (float) ($first['amount'] ?? 0),
                    $first['description'] ?? null,
                ) + [
                    'fridge' => $fridges->get($first['terminalId'] ?? null)
                        ?? $first['terminalName']
                        ?? 'Не определено',
                    'units' => $group->count(),
                    'revenue' => (float) $group->sum('amount'),
                ];
            })
            ->sortByDesc('units')
            ->take($limit)
            ->values();
    }

    private function localDate(array $transaction): Carbon
    {
        return Carbon::parse($transaction['transactionDate'])->setTimezone($this->timezone());
    }

    private function now(): Carbon
    {
        return Carbon::now($this->timezone());
    }

    private function today(): Carbon
    {
        return $this->now()->startOfDay();
    }

    private function timezone(): string
    {
        return (string) config('services.business_cloud.timezone', 'Asia/Almaty');
    }
}
