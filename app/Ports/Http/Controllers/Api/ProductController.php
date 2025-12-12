<?php

namespace App\Ports\Http\Controllers\Api;

use App\Infrastructure\Models\Product;
use App\Ports\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Детальнее о продукте, можно получить все.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function find(int $id): JsonResponse
    {
        $product = Product::whereVisible($id);

        return $this->response(
            data: $product,
        );
    }

    /**
     * Список всех товаров.
     *
     * @return JsonResponse
     */
    public function getAll(): JsonResponse
    {
        $products = Product::whereVisible();

        return $this->response(
            data: $products,
        );
    }
}
