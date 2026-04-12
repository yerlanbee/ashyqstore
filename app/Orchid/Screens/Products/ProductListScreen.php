<?php

namespace App\Orchid\Screens\Products;

use App\Infrastructure\Models\Product;
use App\Orchid\Layouts\Product\ProductListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Group;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;

class ProductListScreen extends Screen
{
    public function query(): iterable
    {
        $products = Product::with(['category', 'fridge'])
            ->filters()
            ->orderByDesc('id')
            ->paginate();

        return [
            'products' => $products,
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
}
