<?php
namespace App\Orchid\Screens\Transaction;

use App\Infrastructure\Repositories\Contracts\TransactionRepositoryInterface;
use App\Orchid\Layouts\Transaction\TransactionFilterLayout;
use App\Orchid\Layouts\Transaction\TransactionListLayout;
use App\Orchid\Layouts\Transaction\TransactionSummaryLayout;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Orchid\Screen\Screen;

class TransactionListScreen extends Screen
{
    /**
     * @param TransactionRepositoryInterface $repository
     */
    public function __construct(
        protected TransactionRepositoryInterface $repository
    ) {}

    public function name(): ?string
    {
        return 'Транзакции';
    }

    public function description(): ?string
    {
        return 'Список транзакций из внешнего сервиса';
    }

    /**
     * @param Request $request
     * @return iterable
     */
    public function query(Request $request): iterable
    {
        $this->validateRequest($request);

        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('pageSize', 10);

        $filters = $this->prepareApiFilters($request, $page);
        $rows = $this->repository->getEnrichedTransactions($filters);

        $rows = $this->applyFilters($rows, $request);

        return [
            'transactions'   => new LengthAwarePaginator(
                $rows->forPage($page, $perPage),
                $rows->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            ),
            'filters'        => [
                'pageSize'       => $perPage,
                'dateTimeFrom'   => $filters['dateTimeFrom'],
                'dateTimeTo'     => $filters['dateTimeTo'],
                'terminalId'     => $request->input('terminalId'),
                'paymentMethods' => $filters['paymentMethods'] ?? [],
            ],
            'filtersSummary' => $this->formatFiltersSummary($filters, $request->input('search')),
            'summary'        => [
                'totalAmount' => $rows->sum(fn($r) => $r['amount'] * $r['count']),
                'totalCount'  => $rows->sum('count'),
            ],
        ];
    }

    public function layout(): iterable
    {
        return [
            TransactionSummaryLayout::class,
            TransactionFilterLayout::class,
            TransactionListLayout::class,
        ];
    }

    /**
     * Применить поиск по коллекции и сортировка
     */
    private function applyFilters(Collection $rows, Request $request): Collection
    {
        // Поиск
        if ($search = $request->input('search')) {
            $search = Str::lower($search);
            $rows = $rows->filter(fn($item) =>
                Str::contains(Str::lower($item['product_name']), $search) ||
                Str::contains(Str::lower((string)$item['product_code']), $search)
            );
        }

        // Сортировка
        $sort = $request->input('sort', '-paid_at');
        $desc = str_starts_with($sort, '-');
        $column = ltrim($sort, '-');

        return $desc
            ? $rows->sortByDesc($column, SORT_NATURAL | SORT_FLAG_CASE)
            : $rows->sortBy($column, SORT_NATURAL | SORT_FLAG_CASE);
    }

    /**
     * Формирование фильтров для API репозитория
     */
    private function prepareApiFilters(Request $request, int $page): array
    {
        return [
            'pageSize'       => (int) $request->input('pageSize', 10),
            'dateTimeFrom'   => $this->formatToIso($request->input('dateTimeFrom')),
            'dateTimeTo'     => $this->formatToIso($request->input('dateTimeTo'), true),
            'terminalIds'    => $request->filled('terminalId') ? [$request->input('terminalId')] : [],
            'page'           => $page,
            'paymentMethods' => array_filter(array_map('intval', (array) $request->input('paymentMethods', []))) ?: null,
        ];
    }

    /**
     * @param string|null $date
     * @param bool $isEnd
     * @return string
     */
    private function formatToIso(?string $date, bool $isEnd = false): string
    {
        $dt = $date ? Carbon::parse($date) : now();
        $dt = $dt->utc();
        return ($isEnd ? $dt->endOfDay() : $dt->startOfDay())->format('Y-m-d\TH:i:s.v\Z');
    }

    /**
     * @param array $filters
     * @param string|null $search
     * @return string
     */
    private function formatFiltersSummary(array $filters, ?string $search): string
    {
        $parts = [
            "Размер: {$filters['pageSize']}",
            "От: " . Carbon::parse($filters['dateTimeFrom'])->format('d.m.Y'),
            "До: " . Carbon::parse($filters['dateTimeTo'])->format('d.m.Y'),
        ];

        if ($search) $parts[] = "Поиск: $search";
        if (!empty($filters['terminalIds'])) $parts[] = "Терминал: {$filters['terminalIds'][0]}";

        return implode(' | ', $parts);
    }

    /**
     * @param Request $request
     * @return void
     */
    private function validateRequest(Request $request): void
    {
        $request->validate([
            'dateTimeFrom' => 'nullable|date|before_or_equal:dateTimeTo',
            'dateTimeTo'   => 'nullable|date|after_or_equal:dateTimeFrom',
            'terminalId'   => 'nullable|uuid|exists:fridges,uuid',
        ]);
    }
}
