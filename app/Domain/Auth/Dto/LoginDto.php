<?php
declare(strict_types=1);

namespace App\Domain\Auth\Dto;

use App\Domain\Shared\ValueObject\PhoneVO;
use Illuminate\Support\Facades\Hash;

class LoginDto
{
    public function __construct(
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
