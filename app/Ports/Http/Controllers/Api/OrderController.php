<?php

namespace App\Ports\Http\Controllers\Api;

use App\Infrastructure\Models\Guest;
use App\Infrastructure\Models\Order;
use App\Infrastructure\Support\Enums\OrderStatus;
use App\Ports\Http\Controllers\Controller;
use App\Ports\Http\Requests\CreateOrderRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    /**
     * Добавление товаров в заказ.
     *
     * @param CreateOrderRequest $request
     * @return JsonResponse
     */
    public function basketAdd(CreateOrderRequest $request): JsonResponse
    {
        $dto = $request->toDto();

        $guest = Guest::whereUuid($dto->guest->guestId);

        if (! $guest) {
            // Создаём нового гостя
            $guest = Guest::createNew($dto->guest);
        }

        $order = Order::query()->firstOrCreate(
            [
                'guest_id' => $guest->id,
                'status' => OrderStatus::DRAFT->value,
            ],
            [
                'total_price' => Order::calculateTotalPrice($dto),
            ]
        );

        $order->products()->sync($dto->items);

        return $this->response(code: Response::HTTP_CREATED);
    }

    /**
     * Детальнее о заказе.
     *
     * @param int $orderId
     * @return JsonResponse
     */
    public function orderDetail(int $orderId): JsonResponse
    {
        $order = Order::findById($orderId);

        abort_if(!$order || $order->isDeleted(), Response::HTTP_NOT_FOUND);

        $order->load([
            'guest',
            'products' => fn ($q) => $q->select('products.id', 'products.name', 'products.price')->withPivot('quantity')
        ]);

        return $this->response(data: [
            'order' => $order->id,
            'status' => OrderStatus::from($order->status)->toHumane(),
            'guest' => $order->guest->uuid,
            'products' => $order->getProducts()
        ]);
    }

    /**
     * Нужно дергать перед тем как отправлять запрос на оплату.
     *
     * @param int $orderId
     * @return JsonResponse
     */
    public function pay(int $orderId): JsonResponse
    {
        $order = Order::findById($orderId);

        abort_if(!$order, Response::HTTP_NOT_FOUND);

        $order->setPending();

        return $this->response();
    }

    /**
     * Удалить заказ.
     *
     * @param int $orderId
     * @return JsonResponse
     */
    public function delete(int $orderId): JsonResponse
    {
        $order = Order::findById($orderId);

        abort_if(!$order, Response::HTTP_NOT_FOUND);

        $order->setDeleted();

        return $this->response();
    }
}
