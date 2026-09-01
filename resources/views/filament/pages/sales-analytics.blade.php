@php
    $fmt = fn ($v, $d = 0) => number_format((float) $v, $d, '.', ' ');

    // Классы пишем целиком: интерполяция вида text-$color-600 вырезается при сборке Tailwind.
    $trend = function (?float $delta) {
        if ($delta === null) {
            return ['icon' => 'heroicon-m-minus-small', 'class' => 'text-gray-500 dark:text-gray-400', 'text' => 'нет базы для сравнения'];
        }

        if ($delta > 0) {
            return ['icon' => 'heroicon-m-arrow-trending-up', 'class' => 'text-success-600 dark:text-success-400', 'text' => '+' . $delta . '%'];
        }

        if ($delta < 0) {
            return ['icon' => 'heroicon-m-arrow-trending-down', 'class' => 'text-danger-600 dark:text-danger-400', 'text' => $delta . '%'];
        }

        return ['icon' => 'heroicon-m-minus-small', 'class' => 'text-gray-500 dark:text-gray-400', 'text' => 'без изменений'];
    };

    $unitsTrend = $trend($delta['units']);
    $revenueTrend = $trend($delta['revenue']);
    $maxUnits = max(1, collect($daily)->max('units'));

    // При 30 днях подписи наезжают друг на друга — показываем через одну.
    $labelEvery = count($daily) > 14 ? 2 : 1;
@endphp

<x-filament-panels::page>
    <x-filament::section icon="heroicon-o-funnel" icon-color="primary" collapsible>
        <x-slot name="heading">Фильтры</x-slot>
        <form wire:submit.prevent>{{ $this->form }}</form>
    </x-filament::section>

    <div
        wire:loading.delay
        class="flex items-center gap-3 rounded-xl bg-primary-50 px-6 py-4 text-sm font-medium text-primary-700 ring-1 ring-primary-600/20 dark:bg-primary-500/10 dark:text-primary-400 dark:ring-primary-400/30"
    >
        <x-filament::loading-indicator class="h-5 w-5" />
        Считаем аналитику по данным Business Cloud…
    </div>

    <div wire:loading.class="opacity-40" class="flex flex-col gap-6 transition-opacity">
    {{-- Сегодня против вчера --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Продано сегодня</div>
            <div class="mt-2 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                {{ $today['units'] }} <span class="text-lg font-normal text-gray-500">шт.</span>
            </div>
            <div class="mt-2 flex items-center gap-1.5 text-sm {{ $unitsTrend['class'] }}">
                <x-filament::icon :icon="$unitsTrend['icon']" class="h-4 w-4" />
                <span class="font-medium">{{ $unitsTrend['text'] }}</span>
            </div>
            <div class="mt-1 text-xs text-gray-500">
                вчера к этому часу {{ $yesterday_so_far['units'] }} шт., за весь день {{ $yesterday['units'] }} шт.
            </div>
        </div>

        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Выручка сегодня</div>
            <div class="mt-2 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                {{ $fmt($today['revenue']) }} <span class="text-lg font-normal text-gray-500">₸</span>
            </div>
            <div class="mt-2 flex items-center gap-1.5 text-sm {{ $revenueTrend['class'] }}">
                <x-filament::icon :icon="$revenueTrend['icon']" class="h-4 w-4" />
                <span class="font-medium">{{ $revenueTrend['text'] }}</span>
            </div>
            <div class="mt-1 text-xs text-gray-500">
                вчера к этому часу {{ $fmt($yesterday_so_far['revenue']) }} ₸, за весь день {{ $fmt($yesterday['revenue']) }} ₸
            </div>
        </div>

        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">За {{ $period['days'] }} дн.</div>
            <div class="mt-2 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                {{ $period['units'] }} <span class="text-lg font-normal text-gray-500">шт.</span>
            </div>
            <div class="mt-2 text-sm text-gray-500">{{ $fmt($period['revenue']) }} ₸</div>
            <div class="mt-1 text-xs text-gray-500">
                в среднем {{ $fmt($period['units'] / max(1, $period['days']), 1) }} шт./день
            </div>
        </div>

        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Средний чек</div>
            <div class="mt-2 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                {{ $fmt($period['avg_check'], 2) }} <span class="text-lg font-normal text-gray-500">₸</span>
            </div>
            <div class="mt-2 text-sm text-gray-500">сегодня {{ $fmt($today['avg_check'], 2) }} ₸</div>
        </div>
    </div>

    {{-- Динамика по дням --}}
    <x-filament::section icon="heroicon-o-chart-bar" icon-color="primary">
        <x-slot name="heading">Продажи по дням</x-slot>
        <x-slot name="description">Высота столбца — количество проданных товаров</x-slot>

        <div class="flex items-end justify-between gap-2 overflow-x-auto pb-2" style="height: 200px">
            @foreach ($daily as $index => $day)
                <div class="flex min-w-[2.5rem] flex-1 flex-col items-center justify-end gap-2">
                    <div class="text-xs font-semibold text-gray-950 dark:text-white">
                        {{ $day['units'] ?: '' }}
                    </div>
                    <div
                        @class([
                            'w-full rounded-t-md transition-all',
                            'bg-primary-500 hover:bg-primary-600' => $day['units'] > 0,
                            'bg-gray-200 dark:bg-gray-700' => $day['units'] === 0,
                        ])
                        style="height: {{ max(2, (int) round($day['units'] / $maxUnits * 130)) }}px"
                        title="{{ $day['label'] }} — {{ $day['units'] }} шт., {{ $fmt($day['revenue']) }} ₸"
                    ></div>
                    @if ($index % $labelEvery === 0)
                        <div class="whitespace-nowrap text-xs text-gray-500">{{ $day['label'] }}</div>
                        <div class="text-[10px] uppercase text-gray-400">{{ $day['weekday'] }}</div>
                    @else
                        <div class="h-[2.1rem]"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </x-filament::section>

    {{-- Топ товаров --}}
    <x-filament::section icon="heroicon-o-trophy" icon-color="primary">
        <x-slot name="heading">Самые продаваемые товары</x-slot>
        <x-slot name="description">За выбранный период, по количеству продаж</x-slot>

        <div class="overflow-x-auto -mx-6">
            <table class="w-full text-start text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10">
                        <th class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">#</th>
                        <th class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">Товар</th>
                        <th class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">Категория</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Продано</th>
                        <th class="px-6 py-3 text-end text-xs font-semibold uppercase tracking-wider text-gray-500">Выручка</th>
                        <th class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">Доля</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse ($top as $index => $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-6 py-3 text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-3 font-medium text-gray-950 dark:text-white">
                                {{ $item['name'] }}
                                @if ($item['shelf'])
                                    <div class="text-xs font-normal text-gray-500">Полка {{ $item['shelf'] }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <span class="fi-badge inline-flex items-center rounded-md bg-info-50 px-2 py-1 text-xs font-medium text-info-700 ring-1 ring-inset ring-info-600/20 dark:bg-info-500/10 dark:text-info-400 dark:ring-info-400/30">
                                    {{ $item['category'] }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center font-semibold text-gray-950 dark:text-white">{{ $item['units'] }}</td>
                            <td class="whitespace-nowrap px-6 py-3 text-end font-semibold text-gray-950 dark:text-white">
                                {{ $fmt($item['revenue']) }} <span class="font-normal text-gray-500">₸</span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                        <div class="h-full rounded-full bg-primary-500" style="width: {{ round($item['units'] / max(1, $period['units']) * 100) }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ round($item['units'] / max(1, $period['units']) * 100, 1) }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <x-filament::icon icon="heroicon-o-inbox" class="h-12 w-12 text-gray-400" />
                                    <div class="text-sm text-gray-500">Нет продаж за выбранный период</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <div class="text-xs text-gray-500">
        Данные Business Cloud, кэш 5 минут. Обновлено {{ $generated_at->format('d.m.Y H:i') }}
        ({{ config('services.business_cloud.timezone') }})
    </div>
    </div>
</x-filament-panels::page>
