<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Infrastructure\Models\Fridge;
use App\Infrastructure\Models\Product;
use App\Infrastructure\Repositories\Contracts\TransactionRepositoryInterface;
use App\Infrastructure\Services\Contracts\BusinessClodServiceContract;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ImportBusinessCloudProducts extends Command
{
    protected $signature = 'bc:import-products
        {--fresh : Удалить все товары перед импортом}
        {--days=30 : За сколько дней смотреть продажи для определения цены и холодильника}';

    protected $description = 'Импортировать справочник товаров из Business Cloud';

    public function handle(
        BusinessClodServiceContract $api,
        TransactionRepositoryInterface $transactions,
    ): int {
        $items = collect($api->getProducts());

        if ($items->isEmpty()) {
            $this->error('Business Cloud вернул пустой справочник — импорт отменён.');

            return self::FAILURE;
        }

        $this->info("Получено товаров из Business Cloud: {$items->count()}");

        $days = max(1, (int) $this->option('days'));
        $sales = $transactions->getRawForPeriod(now()->subDays($days)->startOfDay(), now()->endOfDay());
        $this->info("Транзакций за {$days} дн. для определения цены и холодильника: {$sales->count()}");

        $inferred = $this->inferFromSales($sales);

        if ($this->option('fresh')) {
            $existing = Product::query()->count();

            if ($existing > 0 && ! $this->confirm("Будет удалено товаров: {$existing}. Продолжить?", ! $this->input->isInteractive())) {
                $this->warn('Отменено.');

                return self::FAILURE;
            }

            Product::query()->delete();
            $this->warn("Удалено товаров: {$existing}");
        }

        $created = 0;
        $updated = 0;
        $bar = $this->output->createProgressBar($items->count());

        foreach ($items as $item) {
            $externalId = $item['id'] ?? null;

            if (! $externalId) {
                continue;
            }

            $name = $this->cleanName((string) ($item['nameRU'] ?? $item['nameKZ'] ?? ''));
            $product = Product::query()->firstOrNew(['external_id' => $externalId]);
            $hint = $inferred[$externalId] ?? null;

            $product->name = $name !== '' ? $name : "Без названия {$externalId}";
            $product->uuid ??= (string) Str::uuid();

            // Не затираем то, что администратор мог поправить руками.
            $product->code ??= $this->parseShelf($name);
            $product->fridge_id ??= $hint['fridge_id'] ?? null;

            if (! $product->price) {
                $product->price = $hint['price'] ?? 0;
            }

            $product->exists ? $updated++ : $created++;
            $product->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['Создано', 'Обновлено', 'Без цены', 'Без холодильника'], [[
            $created,
            $updated,
            Product::query()->where('price', 0)->count(),
            Product::query()->whereNull('fridge_id')->count(),
        ]]);

        $this->info('Готово. Категории Business Cloud не отдаёт — их нужно проставить в админке.');

        return self::SUCCESS;
    }

    /**
     * Business Cloud не отдаёт ни цену, ни привязку к терминалу,
     * поэтому восстанавливаем их из истории продаж.
     *
     * @return array<string, array{price: float|null, fridge_id: int|null}>
     */
    private function inferFromSales(Collection $sales): array
    {
        $fridgesByUuid = Fridge::query()->pluck('id', 'uuid');
        $result = [];

        foreach ($sales->groupBy('productId') as $productId => $group) {
            if (! $productId) {
                continue;
            }

            $amounts = $group->pluck('amount')->filter()->countBy();
            $terminals = $group->pluck('terminalId')->filter()->countBy();

            $result[$productId] = [
                'price' => $amounts->isEmpty() ? null : (float) $amounts->sortDesc()->keys()->first(),
                'fridge_id' => $terminals->isEmpty() ? null : $fridgesByUuid[$terminals->sortDesc()->keys()->first()] ?? null,
            ];
        }

        return $result;
    }

    /** В справочнике безымянные позиции приходят как «[За 589]». */
    private function cleanName(string $name): string
    {
        return trim(preg_replace('/^\[|\]$/u', '', trim($name)));
    }

    /** Номер полки — единственная структурированная часть названия. */
    private function parseShelf(string $name): ?string
    {
        return preg_match('/Полка\s*(\d+)/ui', $name, $m) ? $m[1] : null;
    }
}
