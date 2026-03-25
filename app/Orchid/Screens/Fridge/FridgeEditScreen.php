<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Fridge;

use App\Infrastructure\Models\Fridge;
use App\Orchid\Layouts\Fridge\FridgeEditLayout;
use App\Orchid\Layouts\Role\RolePermissionLayout;
use App\Orchid\Layouts\User\UserEditLayout;
use App\Orchid\Layouts\User\UserPasswordLayout;
use App\Orchid\Layouts\User\UserRoleLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Orchid\Access\Impersonation;
use Orchid\Platform\Models\User;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class FridgeEditScreen extends Screen
{
    /**
     * @var $fridge
     */
    public $fridge;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Fridge $fridge): iterable
    {
        return [
            'fridge' => $fridge,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->fridge->exists ? 'Редактирование' : 'Создание';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return null;
    }

    public function permission(): ?iterable
    {
        return [
            'platform.systems.fridges',
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make(__('Удалить'))
                ->icon('bs.trash3')
                ->confirm(__('Вы действительно хотите удалить?'))
                ->method('remove')
                ->canSee($this->fridge->exists),

            Button::make(__('Сохранить'))
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    /**
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::block(FridgeEditLayout::class)
                ->commands(
                    Button::make(__('Сохранить'))
                        ->type(Color::BASIC)
                        ->icon('bs.check-circle')
                        ->canSee($this->fridge->exists)
                        ->method('save')
                )
        ];
    }

    /**
     * @return RedirectResponse
     */
    public function save(Fridge $fridge, Request $request)
    {
        $request->validate([
            'fridge.uuid' => [
                'required',
                Rule::unique(Fridge::class, 'uuid')->ignore($fridge),
            ],
            'fridge.name' => [
                'required',
            ]
        ]);

        $fridge
            ->fill($request->collect('fridge')->only(['name', 'uuid'])->toArray())
            ->save();

        Toast::info(__('Успешно создали!'));

        return redirect()->route('platform.systems.fridges');
    }

    /**
     * @throws \Exception
     *
     * @return RedirectResponse
     */
    public function remove(User $user)
    {
        $user->delete();

        Toast::info(__('User was removed'));

        return redirect()->route('platform.systems.users');
    }
}
