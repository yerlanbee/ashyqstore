<?php

namespace App\Filament\Pages;

use App\Infrastructure\Models\Fridge;
use App\Infrastructure\Repositories\Contracts\TransactionRepositoryInterface;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Transactions extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $activeNavigationIcon = 'heroicon-s-banknotes';

    protected static ?string $navigationLabel = 'Продажи';

    protected static ?string $title = 'Транзакции';

    protected static ?string $navigationGroup = 'Аналитика';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.transactions';

    public ?array $data = [];

    public int $page = 1;

    public function mount(): void
    {
        $defaultFridge = Fridge::query()->first();

        $this->form->fill([
            'pageSize' => 100,
            'dateTimeFrom' => now()->startOfDay()->format('Y-m-d'),
            'dateTimeTo' => now()->endOfDay()->format('Y-m-d'),
            'terminalId' => $defaultFridge?->uuid,
            'paymentMethods' => [],
            'search' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('search')
                    ->label('Поиск товара')
                    ->placeholder('Название или код...')
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn () => $this->resetPage()),

                Select::make('pageSize')
                    ->label('Размер страницы')
                    ->options([
                        20 => '20',
                        50 => '50',
                        100 => '100',
                        150 => '150',
                        200 => '200',
                    ])
                    ->default(100)
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetPage()),

                DatePicker::make('dateTimeFrom')
                    ->label('Дата от')
                    ->native(false)
                    ->displayFormat('d.m.Y')
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetPage()),

                DatePicker::make('dateTimeTo')
                    ->label('Дата до')
                    ->native(false)
                    ->displayFormat('d.m.Y')
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetPage()),

                Select::make('terminalId')
                    ->label('Холодильник')
                    ->options(fn () => Fridge::query()->orderBy('name')->pluck('name', 'uuid'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetPage()),

                Select::make('paymentMethods')
                    ->label('Методы оплаты')
                    ->multiple()
                    ->options([
                        1 => 'Kaspi',
                        2 => 'Halyk',
                    ])
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetPage()),
            ])
            ->columns(3)
            ->statePath('data');
    }

    public function resetPage(): void
    {
        $this->page = 1;
    }

    public function gotoPage(int $page): void
    {
        $this->page = max(1, $page);
    }

    /**
     * @return array{
     *     rows: Collection,
     *     paginator: LengthAwarePaginator,
     *     summary: array{totalAmount: float, totalCount: int},
     *     filtersSummary: string
     * }
     */
    public function getViewData(): array
    {
        $filters = $this->prepareApiFilters();
        $repository = app(TransactionRepositoryInterface::class);

        $rows = $repository->getEnrichedTransactions($filters);
        $rows = $this->applySearch($rows);
        $rows = $this->applySort($rows);

        $perPage = (int) ($this->data['pageSize'] ?? 100);

        $paginator = new Paginator(
            $rows->forPage($this->page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $this->page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return [
            'rows' => $paginator->items(),
            'paginator' => $paginator,
            'summary' => [
                'totalAmount' => $rows->sum(fn ($r) => $r['amount'] * $r['count']),
                'totalCount' => $rows->sum('count'),
            ],
            'filtersSummary' => $this->formatFiltersSummary($filters),
        ];
    }

    protected function prepareApiFilters(): array
    {
        $paymentMethods = array_filter(
            array_map('intval', (array) ($this->data['paymentMethods'] ?? []))
        );

        return [
            'pageSize' => (int) ($this->data['pageSize'] ?? 100),
            'dateTimeFrom' => $this->formatToIso($this->data['dateTimeFrom'] ?? null, false),
            'dateTimeTo' => $this->formatToIso($this->data['dateTimeTo'] ?? null, true),
            'terminalIds' => filled($this->data['terminalId'] ?? null) ? [$this->data['terminalId']] : [],
            'page' => $this->page,
            'paymentMethods' => $paymentMethods ?: null,
        ];
    }

    protected function applySearch(Collection $rows): Collection
    {
        $search = $this->data['search'] ?? null;

        if (! $search) {
            return $rows;
        }

        $search = Str::lower($search);

        return $rows->filter(fn ($item) =>
            Str::contains(Str::lower($item['product_name'] ?? ''), $search) ||
            Str::contains(Str::lower((string) ($item['product_code'] ?? '')), $search)
        );
    }

    protected function applySort(Collection $rows): Collection
    {
        return $rows->sortByDesc('paid_at', SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    protected function formatToIso(?string $date, bool $isEnd): string
    {
        $dt = $date ? Carbon::parse($date) : now();
        $dt = $dt->utc();

        return ($isEnd ? $dt->endOfDay() : $dt->startOfDay())->format('Y-m-d\TH:i:s.v\Z');
    }

    protected function formatFiltersSummary(array $filters): string
    {
        $parts = [
            "Размер: {$filters['pageSize']}",
            'От: ' . Carbon::parse($filters['dateTimeFrom'])->format('d.m.Y'),
            'До: ' . Carbon::parse($filters['dateTimeTo'])->format('d.m.Y'),
        ];

        if ($search = $this->data['search'] ?? null) {
            $parts[] = "Поиск: {$search}";
        }

        if (! empty($filters['terminalIds'])) {
            $fridge = Fridge::query()->where('uuid', $filters['terminalIds'][0])->first();
            $parts[] = 'Терминал: ' . ($fridge?->name ?? $filters['terminalIds'][0]);
        }

        return implode(' | ', $parts);
    }
}
