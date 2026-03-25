<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Fridge;

use App\Infrastructure\Models\Fridge;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class FridgeListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'fridges';

    /**
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make('name', 'Название')
                ->sort()
                ->cantHide()
                ->filter(Input::make())
                ->render(fn (Fridge $fridge) => $fridge->name),

            TD::make('uuid', __('Уникальный номер'))
                ->sort()
                ->cantHide()
                ->filter(Input::make())
                ->render(fn (Fridge $fridge) => ModalToggle::make($fridge->uuid)
                    ->modal('editFridgeModal')
                    ->modalTitle($fridge->name)
                    ->method('saveFridge')
                    ->asyncParameters([
                        'fridge' => $fridge->id,
                    ])),
            TD::make(__('Действия'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (Fridge $fridge) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Button::make(__('Удалить'))
                            ->icon('bs.trash3')
                            ->confirm(__('Вы действительно хотите удалить?'))
                            ->method('remove', [
                                'id' => $fridge->id,
                            ]),
                    ])),
        ];
    }
}
