<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Products;

use App\Infrastructure\Models\Product;
use App\Orchid\Layouts\Product\ProductEditLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ProductEditScreen extends Screen
{
    /**
     * @var ?Product $product
     */
    public ?Product $product = null;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Product $product): iterable
    {
        return [
            'product' => $product,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->product?->exists ? 'Редактирование продукта' : 'Создание продукта';
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
            Button::make('Сохранить')
                ->icon('bs.check-circle')
                ->method('save'),

            Button::make('Удалить')
                ->icon('bs.trash')
                ->method('remove')
                ->canSee($this->product?->exists),
        ];
    }

    /**
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::block(ProductEditLayout::class)
                ->title('Основная информация'),
        ];
    }

    /**
     * @return RedirectResponse
     */
    public function save(Product $product, Request $request)
    {
        $validated = $request->validate([
            'product.name' => ['required', 'string', 'max:255'],
            'product.category_id' => ['required', 'integer'],
            'product.fridge_id' => ['required', 'integer'],
            'product.code' => ['nullable', 'string', 'max:255'],
            'product.quantity' => ['required', 'integer', 'min:0'],
            'product.price' => ['required', 'numeric', 'min:0'],
            'product.sort' => ['nullable', 'integer', 'min:0'],
            'product.image' => ['nullable', 'string'],
        ]);
        $data = $validated['product'];

        $data['uuid'] = Str::uuid()->toString();

        $product->fill($data)->save();

        Alert::success('Продукт сохранен');

        return redirect()->route('platform.systems.products');
    }

    public function remove(Product $product)
    {
        $product->delete();

        Alert::success('Продукт удален');

        return redirect()->route('platform.products.list');
    }
}
