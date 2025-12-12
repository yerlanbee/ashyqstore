<?php

namespace App\Infrastructure\Support\Dto;

class OrderItemDto
{
    public function __construct(
        public int $productId,
        public int $quantity,
    ) {}
}
