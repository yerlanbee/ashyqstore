<?php

namespace App\Domain\Order\Dto;

class GuestDto
{
    public function __construct(
        public string $guestId,
        public string $userAgent,
        public string $ipAddress,
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->guestId,
            'user_agent' => $this->userAgent,
            'ip_address' => $this->ipAddress,
        ];
    }
}
