<x-filament-panels::page>
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

    {{-- Filters --}}
    <x-filament::section
        icon="heroicon-o-funnel"
        icon-color="primary"
    >
        <x-slot name="heading">Фильтры</x-slot>
        <x-slot name="description">{{ $filtersSummary }}</x-slot>

        <form wire:submit.prevent>
            {{ $this->form }}
        </form>
    </x-filament::section>

    {{-- Transactions table --}}
    <x-filament::section
        icon="heroicon-o-table-cells"
        icon-color="primary"
    >
        <x-slot name="heading">Транзакции</x-slot>
        <x-slot name="description">
            Показано {{ count($rows) }} из {{ $paginator->total() }}
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
                                @if (! empty($row['product_code']) && $row['product_code'] !== ($row['amount'] ?? null))
                                    <div class="font-mono text-xs text-gray-500">
                                        #{{ $row['product_code'] }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <span class="fi-badge inline-flex items-center gap-1 rounded-md bg-info-50 px-2 py-1 text-xs font-medium text-info-700 ring-1 ring-inset ring-info-600/20 dark:bg-info-500/10 dark:text-info-400 dark:ring-info-400/30">
                                    {{ $row['category'] ?? 'Без категории' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-3 text-end font-semibold text-gray-950 dark:text-white">
                                {{ number_format((float) ($row['amount'] ?? 0), 2, '.', ' ') }}
                                <span class="text-gray-500">₸</span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <span class="fi-badge inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    × {{ $row['count'] ?? 0 }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-3 text-gray-500 dark:text-gray-400">
                                @php
                                    $paidAt = $row['paid_at'] ?? null;
                                    $formatted = '—';
                                    if ($paidAt) {
                                        try {
                                            $formatted = \Carbon\Carbon::parse($paidAt)->format('d.m.Y H:i:s');
                                        } catch (\Throwable) {
                                            $formatted = (string) $paidAt;
                                        }
                                    }
                                @endphp
                                {{ $formatted }}
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

        @if ($paginator->hasPages())
            <div class="mt-4 flex items-center justify-between gap-3 border-t border-gray-200 pt-4 dark:border-white/10">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Страница <span class="font-medium text-gray-950 dark:text-white">{{ $paginator->currentPage() }}</span>
                    из <span class="font-medium text-gray-950 dark:text-white">{{ $paginator->lastPage() }}</span>
                    · всего <span class="font-medium text-gray-950 dark:text-white">{{ $paginator->total() }}</span>
                </div>
                <div class="flex gap-2">
                    <x-filament::button
                        size="sm"
                        color="gray"
                        icon="heroicon-m-chevron-left"
                        :disabled="$paginator->onFirstPage()"
                        wire:click="gotoPage({{ $paginator->currentPage() - 1 }})"
                    >
                        Назад
                    </x-filament::button>
                    <x-filament::button
                        size="sm"
                        color="gray"
                        icon="heroicon-m-chevron-right"
                        icon-position="after"
                        :disabled="! $paginator->hasMorePages()"
                        wire:click="gotoPage({{ $paginator->currentPage() + 1 }})"
                    >
                        Вперёд
                    </x-filament::button>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
