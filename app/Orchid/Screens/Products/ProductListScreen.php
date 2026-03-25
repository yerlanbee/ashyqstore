<?php

namespace App\Orchid\Screens\Products;

use App\Infrastructure\Models\Product;
use App\Orchid\Layouts\Product\ProductListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class ProductListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'products' => Product::with(['category', 'fridge'])->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Продукты';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Создать')
                ->icon('bs.plus')
                ->route('platform.systems.products.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            ProductListLayout::class,
        ];
    }

    public function saveFridge(Request $request, Product $fridge): void
    {
        $fridge->fill($request->input('product'))->save();

        Toast::info('Изменения сохранены');
    }

    public function remove(Request $request): void
    {
        Product::findOrFail($request->get('id'))->delete();

        Toast::info(__('Успешно удалили'));
    }
}
