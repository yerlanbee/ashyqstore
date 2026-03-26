<?php

namespace App\Orchid\Screens\Category;

use App\Infrastructure\Models\Category;
use App\Orchid\Layouts\Category\CategoryEditLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class CategoryEditScreen extends Screen
{
    public ?Category $category = null;

    public function query(Category $category): iterable
    {
        return [
            'category' => $category,
        ];
    }

    public function name(): ?string
    {
        return $this->category?->exists
            ? 'Редактирование категории'
            : 'Создание категории';
    }

    public function description(): ?string
    {
        return $this->category?->exists
            ? 'Изменение данных категории'
            : 'Новая категория';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Сохранить')
                ->icon('bs.check-circle')
                ->method('save'),

            Button::make('Удалить')
                ->icon('bs.trash')
                ->method('remove')
                ->canSee($this->category?->exists),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::block(CategoryEditLayout::class)
                ->title('Основная информация'),
        ];
    }

    public function save(Category $category, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category.name' => ['required', 'string', 'max:255'],
            'category.sort' => ['required', 'integer', 'min:0'],
            'category.image' => ['nullable', 'string', 'max:255'],
            'category.is_visible' => ['required', 'boolean'],
        ]);

        $data = $validated['category'];
        $data['uuid'] = (string) Str::uuid();

        $category->fill($data)->save();

        Alert::success('Категория сохранена');

        return redirect()->route('platform.systems.categories');
    }

    public function remove(Category $category): RedirectResponse
    {
        $category->delete();

        Alert::success('Категория удалена');

        return redirect()->route('platform.systems.categories');
    }
}
