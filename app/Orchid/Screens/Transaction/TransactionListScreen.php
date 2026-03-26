<?php

namespace App\Orchid\Screens\Transaction;

use App\Infrastructure\Services\BusinessCloudService;
use App\Orchid\Layouts\Transaction\TransactionFilterLayout;
use App\Orchid\Layouts\Transaction\TransactionListLayout;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class TransactionListScreen extends Screen
{
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
        $page = max(1, (int) $request->get('page', 1));
        $filters = $this->buildFilters($request, $page);

        $service = new BusinessCloudService();
        $response = $service->getTransactions($filters);

        $items = collect($response['items']);
        $total = (int) ($response['total'] ?? data_get($response, 'meta.total', $items->count()));

        $rows = $items->map(function ($item) {
            return [
                'name' => data_get($item, 'terminalName') ?? 'Без названия',
                'amount' => data_get($item, 'amount') ?? 0,
                'paid_at' => data_get($item, 'transactionDate') ?? '-'
            ];
        });

        $transactions = new LengthAwarePaginator(
            $rows,
            $total,
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
        ];
    }

    public function layout(): iterable
    {
        return [
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
}
