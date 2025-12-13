<?php
declare(strict_types=1);

namespace App\Domain\Order\Services;

use App\Domain\Order\Dto\OrderDetailDto;
use App\Domain\Order\Enums\OrderStatus;
use App\Infrastructure\Models\Guest;
use App\Infrastructure\Models\Order;
use App\Infrastructure\Support\Core\CustomException;

final class OrderDetailService
{
    public function handle(Order $order): OrderDetailDto
    {
        $guest = Guest::whereUuid($order->guest->uuid);

        if (!$guest)
        {
            new CustomException('Guest not found');
        }

        return new OrderDetailDto(
            $order->id,
            OrderStatus::from($order->status),
            $guest,
            $order->getProducts()->toArray()
        );
    }
}
