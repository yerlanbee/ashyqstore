<?php

namespace App\Orchid\Screens\Transaction;

use App\Infrastructure\Models\Fridge;
use App\Infrastructure\Models\Product;
use App\Infrastructure\Services\BusinessCloudService;
use App\Infrastructure\Services\Contracts\BusinessClodServiceContract;
use App\Orchid\Layouts\Transaction\TransactionFilterLayout;
use App\Orchid\Layouts\Transaction\TransactionListLayout;
use App\Orchid\Layouts\Transaction\TransactionSummaryLayout;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Orchid\Screen\Screen;

class TransactionListScreen extends Screen
{
    public BusinessClodServiceContract $service;

    public function __construct()
    {
        $this->service = new BusinessCloudService();
    }

    private Collection $items;

    private array $response;

    public function name(): ?string
    {
        return 'Транзакции';
    }

    public function description(): ?string
    {
        return 'Список транзакций';
    }

    /**
     * @param Request $request
     * @return iterable
     * @throws ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function query(Request $request): iterable
    {
        $this->validateRequest($request);

        $page = $request->input('page', 1);
        $filters = $this->buildFilters($request, $page);

        $this->response = $this->service->getTransactions($filters);

        $this->items = collect($this->response['items'] ?? []);

        $rows = $this->items
            ->groupBy(fn ($item) => (string) (data_get($item, 'amount') ?? 0))
            ->map(function ($transactions, $amount) {
                $transaction = $transactions->first();
                $paidAt = $transaction['transactionDate'] ?
                    Carbon::parse($transaction['transactionDate'])->addHours(5)->format('d.m.Y H:i:s')
                    : '-';

                /**
                 * @var Product $product
                 */
                $product = Product::whereCode($amount)->first();

                /**
                 * @var Fridge $fridge
                 */
                $fridge = isset($transaction['terminalName'])
                    ? Fridge::whereCode($transaction['terminalName'])->first()
                    : 'Название не определено';

                return [
                    'name' => $fridge->name,
                    'amount' => (float) $amount,
                    'product_name' => $product?->name ?? '-',
                    'count' => $transactions->count(),
                    'paid_at' => $paidAt,
                ];
            })
            ->values();

        $transactions = new LengthAwarePaginator(
            $rows,
            $rows->count(),
            $filters['pageSize'],
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return [
            'filters' => [
                'pageSize' => $filters['pageSize'],
                'dateTimeFrom' => $filters['dateTimeFrom'],
                'dateTimeTo' => $filters['dateTimeTo'],
                'terminalId' => $filters['terminalIds'][0] ?? null,
                'paymentMethods' => $filters['paymentMethods'] ?? [],
            ],
            'filtersSummary' => $this->buildFiltersSummary($filters),
            'transactions' => $transactions,
            'summary' => [
                'totalAmount' => $this->response['totalAmount'] ?? 0,
                'totalCount' => $this->response['totalCount'] ?? 0,
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

    private function buildFilters(Request $request, int $page): array
    {
        $pageSize = max(1, (int) $request->input('pageSize', 10));
        $terminalId = $request->input('terminalId');

        $paymentMethods = array_values(array_filter(
            array_map('intval', (array) $request->input('paymentMethods', []))
        ));

        return [
            'pageSize' => $pageSize,
            'dateTimeFrom' => $this->toIsoUtc($request->input('dateTimeFrom')),
            'dateTimeTo' => $this->toIsoUtc($request->input('dateTimeTo'), true),
            'terminalIds' => $terminalId ? [$terminalId] : [],
            'page' => $page,
            'paymentMethods' => $paymentMethods ?: null,
        ];
    }

    private function toIsoUtc(mixed $date, bool $end = false): string
    {
        $carbon = $date
            ? Carbon::parse($date)
            : now();

        $carbon = $carbon->utc();

        $carbon = $end
            ? $carbon->endOfDay()
            : $carbon->startOfDay();

        return $carbon->format('Y-m-d\TH:i:s.v\Z');
    }


    private function buildFiltersSummary(array $filters): string
    {
        $parts = [];

        $parts[] = 'Размер: ' . $filters['pageSize'];

        if (!empty($filters['terminalIds'][0])) {
            $parts[] = 'Холодильник: ' . $filters['terminalIds'][0];
        }

        if (!empty($filters['paymentMethods'])) {
            $parts[] = 'Оплата: ' . implode(', ', $filters['paymentMethods']);
        }

        $parts[] = 'От: ' . Carbon::parse($filters['dateTimeFrom'])->format('d.m.Y H:i');
        $parts[] = 'До: ' . Carbon::parse($filters['dateTimeTo'])->format('d.m.Y H:i');

        return implode(' | ', $parts);
    }

    private function validateRequest(Request $request): void
    {
        $request->validate([
            'dateTimeFrom' => ['nullable', 'date', 'before_or_equal:dateTimeTo'],
            'dateTimeTo' => ['nullable', 'date', 'after_or_equal:dateTimeFrom'],

            'terminalId' => ['nullable', 'uuid', 'exists:fridges,uuid'],
        ], [
            'dateTimeFrom.before_or_equal' => 'Дата начала должна быть меньше или равна дате окончания',
            'dateTimeTo.after_or_equal' => 'Дата окончания должна быть больше или равна дате начала',

            'terminalId.uuid' => 'Неверный формат терминала',
            'terminalId.exists' => 'Холодильник не найден',
        ]);
    }
}
