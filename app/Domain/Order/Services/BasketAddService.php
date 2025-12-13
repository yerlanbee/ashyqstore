<?php
declare(strict_types=1);

namespace App\Domain\Order\Services;

use App\Domain\Order\Dto\OrderDto;
use App\Domain\Order\Enums\OrderStatus;
use App\Infrastructure\Models\Guest;
use App\Infrastructure\Models\Order;

final class BasketAddService
{
    public function handle(OrderDto $dto): void
    {
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
    }
}
