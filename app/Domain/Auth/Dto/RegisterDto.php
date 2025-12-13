<?php

namespace App\Domain\Auth\Dto;

use App\Domain\Shared\ValueObject\PhoneVO;
use Illuminate\Support\Facades\Hash;

class RegisterDto
{
    public function __construct(
        public string $username,
        public PhoneVO $phone,
        public string $password,
    ) {}

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return Hash::make($this->password);
    }
}
