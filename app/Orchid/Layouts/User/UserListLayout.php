<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use Orchid\Platform\Models\User;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Persona;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class UserListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'users';

    /**
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make('name', __('Имя'))
                ->sort()
                ->cantHide()
                ->filter(Input::make())
                ->render(fn (User $user) => $user->name),

            TD::make('email', __('Электронный адрес'))
                ->sort()
                ->cantHide()
                ->filter(Input::make())
                ->render(fn (User $user) => ModalToggle::make($user->email)
                    ->modal('editUserModal')
                    ->modalTitle($user->presenter()->title())
                    ->method('saveUser')
                    ->asyncParameters([
                        'user' => $user->id,
                    ])),

            TD::make(__('Действия'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (User $user) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([

                        Link::make(__('Отредактировать'))
                            ->route('platform.systems.users.edit', $user->id)
                            ->icon('bs.pencil'),

                        Button::make(__('Удалить'))
                            ->icon('bs.trash3')
                            ->confirm(__('Вы действительно хотите удалить?'))
                            ->method('remove', [
                                'id' => $user->id,
                            ]),
                    ])),
        ];
    }
}
