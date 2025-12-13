<?php

namespace App\Domain\Shared\ValueObject;

use InvalidArgumentException;

final class PhoneVO
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function from(string $phone): self
    {
        $normalized = self::normalize($phone);

        if (! self::isValid($normalized)) {
            throw new InvalidArgumentException('Некорректный номер телефона');
        }

        return new self($normalized);
    }

    /**
     * Формат: 7777 777 77 77
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Формат: +7(777)777 77 77
     */
    public function formatted(): string
    {
        return sprintf(
            '+7(%s)%s %s %s',
            substr($this->value, 1, 3),
            substr($this->value, 4, 3),
            substr($this->value, 7, 2),
            substr($this->value, 9, 2),
        );
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private static function normalize(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }

        if (str_starts_with($phone, '8')) {
            $phone = '7' . substr($phone, 1);
        }

        if (strlen($phone) === 10) {
            $phone = '7' . $phone;
        }

        return preg_replace('/\D/', '', $phone);
    }

    private static function isValid(string $phone): bool
    {
        return preg_match('/^7\d{10}$/', $phone) === 1;
    }
}
