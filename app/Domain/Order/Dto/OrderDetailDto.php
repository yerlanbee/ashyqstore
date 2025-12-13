<?php

namespace App\Domain\Order\Dto;

use App\Domain\Order\Enums\OrderStatus;
use App\Infrastructure\Models\Guest;

class OrderDetailDto
{
    public function __construct(
        public int $orderId,
        public OrderStatus $status,
        public Guest $guest,
        public array $products,
    ) {}

    public function toArray(): array
    {
        return (array) $this;
    }
}
