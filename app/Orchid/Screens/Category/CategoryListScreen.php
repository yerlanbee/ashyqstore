<?php

namespace App\Orchid\Screens\Category;

use App\Infrastructure\Models\Category;
use App\Infrastructure\Models\Product;
use App\Orchid\Layouts\Category\CategoryListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class CategoryListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'categories' => Category::query()
                ->orderBy('sort')
                ->orderByDesc('id')
                ->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Категории';
    }

    public function description(): ?string
    {
        return 'Список категорий';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Создать')
                ->icon('bs.plus')
                ->route('platform.systems.categories.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            CategoryListLayout::class,
        ];
    }

    public function remove(Request $request): void
    {
        Category::findOrFail($request->get('id'))->delete();

        Toast::info(__('Успешно удалили'));
    }
}
