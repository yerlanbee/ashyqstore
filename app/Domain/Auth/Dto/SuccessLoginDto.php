<?php

namespace App\Domain\Auth\Dto;

class SuccessLoginDto
{
    public function __construct(
        public int $userId,
        public string $username,
        public string $token
    ) {}

    public function toArray(): array
    {
        return (array) $this;
    }
}
