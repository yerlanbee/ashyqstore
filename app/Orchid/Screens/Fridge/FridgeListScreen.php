<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Fridge;

use App\Infrastructure\Models\Fridge;
use App\Orchid\Layouts\Fridge\FridgeEditLayout;
use App\Orchid\Layouts\Fridge\FridgeListLayout;
use App\Orchid\Layouts\User\UserEditLayout;
use App\Orchid\Layouts\User\UserFiltersLayout;
use App\Orchid\Layouts\User\UserListLayout;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Platform\Models\User;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class FridgeListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'fridges' => Fridge::query()
                ->orderByDesc('id')
                ->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Микромаркеты';
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
            Link::make(__('Добавить'))
                ->icon('bs.plus-circle')
                ->route('platform.systems.fridges.create'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return string[]|\Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            UserFiltersLayout::class,
            FridgeListLayout::class,

            Layout::modal('editFridgeModal', FridgeEditLayout::class)
                ->deferred('loadFridgeOnOpenModal'),
        ];
    }

    /**
     * Loads user data when opening the modal window.
     *
     * @return array
     */
    public function loadFridgeOnOpenModal(Fridge $fridge): iterable
    {
        return [
            'fridge' => $fridge,
        ];
    }

    public function saveFridge(Request $request, Fridge $fridge): void
    {
        $fridge->fill($request->input('fridge'))->save();

        Toast::info('Изменения сохранены');
    }

    public function remove(Request $request): void
    {
        Fridge::findOrFail($request->get('id'))->delete();

        Toast::info(__('Успешно удалили'));
    }
}
