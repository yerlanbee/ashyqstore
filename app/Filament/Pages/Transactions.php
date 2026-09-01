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

    public function mount(): void
    {
        $this->form->fill([
            'dateTimeFrom' => now($this->timezone())->format('Y-m-d'),
            'dateTimeTo' => now($this->timezone())->format('Y-m-d'),
            'terminalIds' => [],
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
                    ->placeholder('Название или полка...')
                    ->live(debounce: 500),

                DatePicker::make('dateTimeFrom')
                    ->label('Дата от')
                    ->native(false)
                    ->displayFormat('d.m.Y')
                    ->live(),

                DatePicker::make('dateTimeTo')
                    ->label('Дата до')
                    ->native(false)
                    ->displayFormat('d.m.Y')
                    ->live(),

                Select::make('terminalIds')
                    ->label('Микромаркеты')
                    ->placeholder('Все микромаркеты')
                    ->multiple()
                    ->options(fn () => Fridge::query()->orderBy('name')->pluck('name', 'uuid'))
                    ->searchable()
                    ->live(),

                Select::make('paymentMethods')
                    ->label('Методы оплаты')
                    ->multiple()
                    ->placeholder('Все методы')
                    ->options([
                        1 => 'Kaspi',
                        2 => 'Halyk',
                    ])
                    ->live(),
            ])
            ->columns(3)
            ->statePath('data');
    }

    /**
     * @return array{
     *     rows: array,
     *     summary: array{totalAmount: float, totalCount: int},
     *     byFridge: Collection,
     *     filtersSummary: string
     * }
     */
    public function getViewData(): array
    {
        $repository = app(TransactionRepositoryInterface::class);

        $raw = $repository->getRawForPeriod(
            $this->dateFrom(),
            $this->dateTo(),
            $this->terminalIds(),
            $this->paymentMethods(),
        );

        $rows = $repository->groupByProduct($raw);
        $rows = $this->applySearch($rows);
        $rows = $this->applySort($rows);

        return [
            'rows' => $rows->all(),
            'summary' => [
                'totalAmount' => (float) $rows->sum('total'),
                'totalCount' => (int) $rows->sum('count'),
            ],
            'byFridge' => $this->summarizeByFridge($rows),
            'filtersSummary' => $this->formatFiltersSummary(),
        ];
    }

    /** Итоги по каждому микромаркету, чтобы их можно было сравнить между собой. */
    protected function summarizeByFridge(Collection $rows): Collection
    {
        return $rows
            ->groupBy('name')
            ->map(fn (Collection $group, string $name) => [
                'name' => $name,
                'units' => (int) $group->sum('count'),
                'revenue' => (float) $group->sum('total'),
                'positions' => $group->count(),
            ])
            ->sortByDesc('revenue')
            ->values();
    }

    /** @return string[] Пустой массив — все микромаркеты. */
    protected function terminalIds(): array
    {
        return array_values(array_filter((array) ($this->data['terminalIds'] ?? [])));
    }

    /** @return int[]|null */
    protected function paymentMethods(): ?array
    {
        $methods = array_filter(array_map('intval', (array) ($this->data['paymentMethods'] ?? [])));

        return $methods ?: null;
    }

    protected function dateFrom(): Carbon
    {
        return $this->parseDate($this->data['dateTimeFrom'] ?? null)->startOfDay();
    }

    protected function dateTo(): Carbon
    {
        return $this->parseDate($this->data['dateTimeTo'] ?? null)->endOfDay();
    }

    /** Границы суток считаем по местному времени: API отдаёт и принимает UTC. */
    protected function parseDate(?string $date): Carbon
    {
        return $date
            ? Carbon::parse($date, $this->timezone())
            : Carbon::now($this->timezone());
    }

    protected function timezone(): string
    {
        return config('services.business_cloud.timezone', 'Asia/Almaty');
    }

    public function formatPaidAt(?string $value): string
    {
        if (! $value) {
            return '—';
        }

        return Carbon::parse($value)->timezone($this->timezone())->format('d.m.Y H:i:s');
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
        return $rows->sortByDesc('count')->values();
    }

    protected function formatFiltersSummary(): string
    {
        $parts = [
            'С ' . $this->dateFrom()->format('d.m.Y'),
            'по ' . $this->dateTo()->format('d.m.Y'),
        ];

        $terminalIds = $this->terminalIds();

        $parts[] = $terminalIds === []
            ? 'Все микромаркеты'
            : Fridge::query()->whereIn('uuid', $terminalIds)->pluck('name')->implode(', ');

        if ($search = $this->data['search'] ?? null) {
            $parts[] = "Поиск: {$search}";
        }

        return implode(' | ', $parts);
    }
}
