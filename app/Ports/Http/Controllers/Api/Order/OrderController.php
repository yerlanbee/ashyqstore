<?php

namespace App\Ports\Http\Controllers\Api\Order;

use App\Domain\Order\Services\BasketAddService;
use App\Domain\Order\Services\OrderDetailService;
use App\Infrastructure\Models\Order;
use App\Ports\Http\Controllers\Controller;
use App\Ports\Http\Requests\Order\CreateOrderRequest;
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
    public function basketAdd(CreateOrderRequest $request,  BasketAddService $service): JsonResponse
    {
        $service->handle($request->toDto());

        return $this->response(code: Response::HTTP_CREATED);
    }

    /**
     * Детальнее о заказе.
     *
     * @param int $orderId
     * @return JsonResponse
     */
    public function orderDetail(int $orderId, OrderDetailService $service): JsonResponse
    {
        $order = Order::findById($orderId);

        abort_if(!$order || $order->isDeleted(), Response::HTTP_NOT_FOUND);

        $detail = $service->handle($order);

        return $this->response(data: $detail->toArray());
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
