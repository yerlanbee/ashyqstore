<?php

namespace App\Domain\Order\Dto;

class OrderItemDto
{
    public function __construct(
        public int $productId,
        public int $quantity,
    ) {}
}
