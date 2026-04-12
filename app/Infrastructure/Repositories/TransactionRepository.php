<?php
namespace App\Infrastructure\Repositories;

use App\Infrastructure\Models\Fridge;
use App\Infrastructure\Models\Product;
use App\Infrastructure\Services\Contracts\BusinessClodServiceContract;
use App\Infrastructure\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Support\Collection;

class TransactionRepository implements TransactionRepositoryInterface
{
    private array $lastResponse = [];

    public function __construct(
        protected BusinessClodServiceContract $apiService
    ) {}

    public function getEnrichedTransactions(array $filters): Collection
    {
        $this->lastResponse = $this->apiService->getTransactions($filters);
        $items = collect($this->lastResponse['items'] ?? []);

        if ($items->isEmpty()) return collect();

        //  Жадная загрузка данных из БД (оптимизация N+1)
        $amounts = $items->pluck('amount')->unique();
        $terminalNames = $items->pluck('terminalName')->unique();
        $products = Product::with('category')->whereIn('code', $amounts)->get()->keyBy('code');
        $fridges  = Fridge::whereIn('code', $terminalNames)->get()->keyBy('code');

        return $items->groupBy(fn ($item) => (string)($item['amount'] ?? 0))
            ->map(function (Collection $group, $amount) use ($products, $fridges) {
                $first = $group->first();
                $product = $products->get($amount);
                $fridge = $fridges->get($first['terminalName'] ?? null);

                return [
                    'name'   => $fridge?->name ?? 'Не определено',
                    'amount'        => (float) $amount,
                    'product_code'  => $product?->code ?? $amount,
                    'product_name'  => $product?->name ?? '-',
                    'category'      => $product?->category?->name ?? 'Без категории',
                    'count'         => $group->count(),
                    'paid_at'       => $first['transactionDate'] ?? null,
                ];
            })->values();
    }

    /**
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'totalAmount' => $this->lastResponse['totalAmount'] ?? 0,
            'totalCount'  => $this->lastResponse['totalCount'] ?? 0,
        ];
    }
}
