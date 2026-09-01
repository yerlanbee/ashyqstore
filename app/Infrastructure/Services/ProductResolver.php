<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Infrastructure\Models\Fridge;
use App\Infrastructure\Models\Product;
use Illuminate\Support\Collection;

/**
 * Определяет товар по цене в нашем каталоге.
 *
 * products.code хранит цену (123 записи из 124), она же приходит в транзакции
 * как amount — это единственная связь между продажей и товаром, пока в
 * каталоге не заполнен external_id.
 */
class ProductResolver
{
    private ?Collection $byFridgeAndPrice = null;

    private ?Collection $fridgeIdByUuid = null;

    /** @return array{name: string, shelf: ?string, category: string} */
    public function resolve(?string $terminalId, float $amount, ?string $description = null): array
    {
        $product = $this->find($terminalId, $amount);

        if ($product === null) {
            return [
                'name' => $description ?: 'Не определено',
                'shelf' => null,
                'category' => 'Без категории',
            ];
        }

        return [
            'name' => $product->name,
            'shelf' => $this->shelfOf($product),
            'category' => $product->category?->name ?? 'Без категории',
        ];
    }

    private function find(?string $terminalId, float $amount): ?Product
    {
        $fridgeId = $terminalId === null ? null : $this->fridgeIdByUuid()->get($terminalId);

        if ($fridgeId === null || $amount <= 0.0) {
            return null;
        }

        return $this->byFridgeAndPrice()->get($this->key((int) $fridgeId, $amount));
    }

    /**
     * В старой схеме products.code хранит цену, а не номер полки — такое
     * за полку не выдаём, иначе в таблице появляется «Полка 589».
     */
    private function shelfOf(Product $product): ?string
    {
        if ($product->code === null || (float) $product->code === (float) $product->price) {
            return null;
        }

        return $product->code;
    }

    private function key(int $fridgeId, float $amount): string
    {
        return $fridgeId . '|' . number_format($amount, 2, '.', '');
    }

    /** @return Collection<string, Product> */
    private function byFridgeAndPrice(): Collection
    {
        // На одну цену в холодильнике заведено до трёх товаров. Берём первый
        // по имени, чтобы подпись не прыгала между обновлениями страницы.
        return $this->byFridgeAndPrice ??= Product::query()
            ->with('category')
            ->whereNotNull('fridge_id')
            ->get()
            ->groupBy(fn (Product $p) => $this->key((int) $p->fridge_id, (float) $p->code))
            ->map(fn (Collection $group) => $group->sortBy('name')->first());
    }

    private function fridgeIdByUuid(): Collection
    {
        return $this->fridgeIdByUuid ??= Fridge::query()->pluck('id', 'uuid');
    }
}
