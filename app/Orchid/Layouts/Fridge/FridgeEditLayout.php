<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Fridge;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class FridgeEditLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('fridge.name')
                ->type('text')
                ->max(255)
                ->required()
                ->title(__('Название'))
                ->placeholder(__('Название')),

            Input::make('fridge.uuid')
                ->type('uuid')
                ->required()
                ->title(__('Уникальный номер'))
                ->placeholder(__('Уникальный номер')),
        ];
    }
}
