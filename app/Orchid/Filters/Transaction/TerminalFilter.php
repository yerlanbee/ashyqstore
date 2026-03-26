<?php

namespace App\Orchid\Filters\Transaction;

use App\Infrastructure\Models\Fridge;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Select;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class TerminalFilter extends Filter
{
    public function name(): string
    {
        return 'Холодильник';
    }

    public function parameters(): array
    {
        return ['terminalId'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function display(): iterable
    {
        return [
            Select::make('terminalId')
                ->fromModel(Fridge::class, 'name', 'uuid')
                ->empty()
                ->value($this->request->get('terminalId'))
                ->title($this->name()),
        ];
    }

    public function value(): string
    {
        $uuid = $this->request->get('terminalId');

        if (!$uuid) {
            return '';
        }

        $fridge = Fridge::query()->where('uuid', $uuid)->first();

        return $this->name() . ': ' . ($fridge?->name ?? $uuid);
    }
}
