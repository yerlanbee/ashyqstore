<?php

namespace App\Infrastructure\Support\Dto;

class OrderDto
{
    public function __construct(
        public GuestDto $guest,
        public array $items,
    ) {}

    public static function fromArray(array $data): self
    {
        $guest = $data['guest'];

        return new self(
            new GuestDto($guest['uuid'], $guest['user_agent'], $guest['ip_address']),
            $data['items'],
        );
    }

    public function getProducts(): array
    {
        return array_map(fn($item) => $item['product_id'], $this->items);
    }
}
