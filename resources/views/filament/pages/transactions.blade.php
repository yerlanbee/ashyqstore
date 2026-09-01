<x-filament-panels::page>
    {{-- Filters --}}
    <x-filament::section
        icon="heroicon-o-funnel"
        icon-color="primary"
        collapsible
    >
        <x-slot name="heading">Фильтры</x-slot>
        <x-slot name="description">{{ $filtersSummary }}</x-slot>

        <form wire:submit.prevent>
            {{ $this->form }}
        </form>
    </x-filament::section>

    {{-- Данные тянутся из внешнего API, ответ занимает секунды — без индикатора
         страница выглядит зависшей. --}}
    <div
        wire:loading.delay
        class="flex items-center gap-3 rounded-xl bg-primary-50 px-6 py-4 text-sm font-medium text-primary-700 ring-1 ring-primary-600/20 dark:bg-primary-500/10 dark:text-primary-400 dark:ring-primary-400/30"
    >
        <x-filament::loading-indicator class="h-5 w-5" />
        Загружаем транзакции из Business Cloud…
    </div>

    <div wire:loading.class="opacity-40" class="flex flex-col gap-6 transition-opacity">
    {{-- Summary cards --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                        <x-filament::icon
                            icon="heroicon-m-banknotes"
                            class="h-5 w-5 text-success-500"
                        />
                        <span>Общая сумма продаж</span>
                    </div>
                    <div class="mt-3 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                        {{ number_format((float) ($summary['totalAmount'] ?? 0), 2, '.', ' ') }}
                        <span class="text-xl text-gray-500 dark:text-gray-400">₸</span>
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        за выбранный период, по всем страницам
                    </div>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-success-50 dark:bg-success-500/10">
                    <x-filament::icon
                        icon="heroicon-o-arrow-trending-up"
                        class="h-6 w-6 text-success-600 dark:text-success-400"
                    />
                </div>
            </div>
        </div>

        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                        <x-filament::icon
                            icon="heroicon-m-shopping-cart"
                            class="h-5 w-5 text-info-500"
                        />
                        <span>Количество продаж</span>
                    </div>
                    <div class="mt-3 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                        {{ (int) ($summary['totalCount'] ?? 0) }}
                        <span class="text-xl text-gray-500 dark:text-gray-400">шт.</span>
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        за выбранный период, по всем страницам
                    </div>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-info-50 dark:bg-info-500/10">
                    <x-filament::icon
                        icon="heroicon-o-shopping-bag"
                        class="h-6 w-6 text-info-600 dark:text-info-400"
                    />
                </div>
            </div>
        </div>
    </div>

    {{-- Разбивка по микромаркетам --}}
    @if ($byFridge->count() > 1)
        @php
            // Класс пишем целиком: md:grid-cols-{{ $n }} вырезается при сборке Tailwind.
            // Больше трёх в ряд не ставим — карточки становятся нечитаемо узкими.
            $gridCols = $byFridge->count() === 2 ? 'md:grid-cols-2' : 'lg:grid-cols-3';
        @endphp
        <div class="grid grid-cols-1 gap-4 {{ $gridCols }}">
            @foreach ($byFridge as $fridge)
                @php
                    $share = ($summary['totalAmount'] ?? 0) > 0
                        ? round($fridge['revenue'] / $summary['totalAmount'] * 100)
                        : 0;
                @endphp
                <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-500/10">
                                <x-filament::icon
                                    icon="heroicon-o-building-storefront"
                                    class="h-6 w-6 text-primary-600 dark:text-primary-400"
                                />
                            </div>
                            <div class="text-base font-semibold text-gray-950 dark:text-white">
                                {{ $fridge['name'] }}
                            </div>
                        </div>
                        <span class="fi-badge inline-flex items-center rounded-md bg-primary-50 px-2.5 py-1 text-sm font-semibold text-primary-700 ring-1 ring-inset ring-primary-600/20 dark:bg-primary-500/10 dark:text-primary-400 dark:ring-primary-400/30">
                            {{ $share }}%
                        </span>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Выручка</div>
                            <div class="mt-1 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                                {{ number_format($fridge['revenue'], 0, '.', ' ') }}
                                <span class="text-lg font-normal text-gray-500">₸</span>
                            </div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Продано</div>
                            <div class="mt-1 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                                {{ $fridge['units'] }}
                                <span class="text-lg font-normal text-gray-500">шт.</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 h-2.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div class="h-full rounded-full bg-primary-500" style="width: {{ $share }}%"></div>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                        <span>{{ $fridge['positions'] }} позиций в продаже</span>
                        <span>доля в общей выручке</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Transactions table --}}
    <x-filament::section
        icon="heroicon-o-table-cells"
        icon-color="primary"
    >
        <x-slot name="heading">Транзакции</x-slot>
        <x-slot name="description">
            Всего позиций: {{ count($rows) }}
        </x-slot>

        <div class="overflow-x-auto -mx-6">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10">
                        <th class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            <div class="flex items-center gap-1.5">
                                <x-filament::icon icon="heroicon-m-building-storefront" class="h-4 w-4" />
                                Микромаркет
                            </div>
                        </th>
                        <th class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            <div class="flex items-center gap-1.5">
                                <x-filament::icon icon="heroicon-m-cube" class="h-4 w-4" />
                                Товар
                            </div>
                        </th>
                        <th class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            <div class="flex items-center gap-1.5">
                                <x-filament::icon icon="heroicon-m-rectangle-stack" class="h-4 w-4" />
                                Категория
                            </div>
                        </th>
                        <th class="px-6 py-3 text-end text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Сумма
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Кол-во
                        </th>
                        <th class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            <div class="flex items-center gap-1.5">
                                <x-filament::icon icon="heroicon-m-clock" class="h-4 w-4" />
                                Время
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse ($rows as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="whitespace-nowrap px-6 py-3 text-gray-950 dark:text-white">
                                {{ $row['name'] ?? 'Не известно' }}
                            </td>
                            <td class="px-6 py-3 font-medium text-gray-950 dark:text-white">
                                {{ $row['product_name'] ?? 'Не известно' }}
                                @if (! empty($row['product_code']))
                                    <div class="font-mono text-xs font-normal text-gray-500">
                                        Полка {{ $row['product_code'] }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <span class="fi-badge inline-flex items-center gap-1 rounded-md bg-info-50 px-2 py-1 text-xs font-medium text-info-700 ring-1 ring-inset ring-info-600/20 dark:bg-info-500/10 dark:text-info-400 dark:ring-info-400/30">
                                    {{ $row['category'] ?? 'Без категории' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-3 text-end font-semibold text-gray-950 dark:text-white">
                                {{ number_format((float) ($row['total'] ?? 0), 2, '.', ' ') }}
                                <span class="text-gray-500">₸</span>
                                @if (($row['count'] ?? 0) > 1)
                                    <div class="text-xs font-normal text-gray-500">
                                        по {{ number_format((float) ($row['amount'] ?? 0), 2, '.', ' ') }} ₸
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-center">
                                <span class="fi-badge inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    × {{ $row['count'] ?? 0 }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-3 text-gray-500 dark:text-gray-400">
                                {{ $this->formatPaidAt($row['paid_at'] ?? null) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <x-filament::icon
                                        icon="heroicon-o-inbox"
                                        class="h-12 w-12 text-gray-400"
                                    />
                                    <div class="text-sm text-gray-500">
                                        Нет транзакций по выбранным фильтрам
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </x-filament::section>
    </div>
</x-filament-panels::page>
