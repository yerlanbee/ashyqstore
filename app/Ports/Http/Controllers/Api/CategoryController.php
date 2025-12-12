<?php

namespace App\Ports\Http\Controllers\Api;

use App\Infrastructure\Models\Category;
use App\Ports\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Для главной страницы. Список категорий.
     *
     * @return JsonResponse
     */
    public function getAll(): JsonResponse
    {
        return $this->response(
            data: Category::getAll()
        );
    }

    /**
     * Детальнее категорий, можно получить список товаров внутри.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function findById(int $id, Request $request): JsonResponse
    {
        $category = Category::findWithProducts($id);

        return $this->response(
            data: $category
        );
    }
}
