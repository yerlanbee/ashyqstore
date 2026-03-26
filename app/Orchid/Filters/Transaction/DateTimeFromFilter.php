<?php

namespace App\Orchid\Filters\Transaction;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\DateTimer;

class DateTimeFromFilter extends Filter
{
    public function name(): string
    {
        return 'Дата от';
    }

    public function parameters(): array
    {
        return ['dateTimeFrom'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder;
    }

    public function display(): iterable
    {
        return [
            DateTimer::make('dateTimeFrom')
                ->title($this->name())
                ->format('Y-m-d') // только дата
                ->allowInput()
                ->value($this->parseDate($this->request->get('dateTimeFrom'))),
        ];
    }

    public function value(): string
    {
        $value = $this->request->get('dateTimeFrom');

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
        return $this->toIso($this->request->get('dateTimeFrom'), false);
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
