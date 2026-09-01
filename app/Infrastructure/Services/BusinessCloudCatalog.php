<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Infrastructure\Services\Contracts\BusinessClodServiceContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Справочник товаров Business Cloud, разобранный по productId.
 *
 * Нужен как запасной источник названий: один и тот же товар заведён в BC
 * несколько раз (своя запись на каждую полку), поэтому связать его с
 * products.external_id один к одному нельзя — а показать название надо.
 */
class BusinessCloudCatalog
{
    private const CACHE_TTL = 3600;

    public function __construct(
        private readonly BusinessClodServiceContract $api,
    ) {}

    /**
     * Только позиции с номером полки в названии. Остальные — это «тенге»
     * и «[За 589]»: названия у них нет, есть только цена, поэтому такие
     * разбираются подбором по цене, а не отсюда.
     *
     * @return Collection<string, array{name: string, shelf: ?string}>
     */
    public function byProductId(): Collection
    {
        return Cache::remember(
            'business_cloud:catalog:shelved',
            self::CACHE_TTL,
            fn () => collect($this->api->getProducts())
                ->filter(fn (array $item) => ! empty($item['id']))
                ->mapWithKeys(fn (array $item) => [
                    $item['id'] => $this->describe((string) ($item['nameRU'] ?? $item['nameKZ'] ?? '')),
                ])
                ->filter(fn (array $item) => $item['shelf'] !== null)
        );
    }

    /** @return array{name: string, shelf: ?string} */
    public function describe(string $raw): array
    {
        // Безымянные позиции приходят как «[За 589]».
        $name = trim(preg_replace('/^\[|\]$/u', '', trim($raw)));

        return [
            'name' => $this->stripShelf($name) ?: $name,
            'shelf' => $this->parseShelf($name),
        ];
    }

    /** Номер полки — единственная структурированная часть названия. */
    public function parseShelf(string $name): ?string
    {
        return preg_match('/Полка\s*(\d+)/ui', $name, $m) ? $m[1] : null;
    }

    /** «Полка 1 - Coca Cola 0.5л» → «Coca Cola 0.5л»: полка живёт в отдельной колонке. */
    private function stripShelf(string $name): string
    {
        $name = preg_replace('/Полка\s*\d+/ui', '', $name);
        // Дефис-разделитель убираем только с пробелами по бокам, иначе пострадает «Big-bob».
        $name = preg_replace('/\s+[-–—]\s+/u', ' ', $name);
        $name = preg_replace('/\s+/u', ' ', $name);

        return trim($name, " \t\n\r\0\x0B-–—");
    }
}
