<?php

namespace App\Orchid\Filters\Transaction;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\DateTimer;

class DateTimeToFilter extends Filter
{
    public function name(): string
    {
        return 'Дата до';
    }

    public function parameters(): array
    {
        return ['dateTimeTo'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder;
    }

    public function display(): iterable
    {
        return [
            DateTimer::make('dateTimeTo')
                ->title($this->name())
                ->format('Y-m-d') // только дата
                ->allowInput()
                ->value($this->parseDate($this->request->get('dateTimeTo'))),
        ];
    }

    public function value(): string
    {
        $value = $this->request->get('dateTimeTo');

        if (!$value) {
            return $this->name() . ': сегодня';
        }

        return $this->name() . ': ' . Carbon::parse($value)->format('d.m.Y');
    }

    /**
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getIso(): string
    {
        return $this->toIso($this->request->get('dateTimeTo'), true);
    }

    private function toIso(?string $date, bool $end): string
    {
        $carbon = $date ? Carbon::parse($date) : now();

        $carbon = $carbon->utc();

        $carbon = $end
            ? $carbon->endOfDay()
            : $carbon->startOfDay();

        return $carbon->format('Y-m-d\TH:i:s.v\Z');
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
