<?php

namespace App\Domain\Order\Enums;

enum OrderStatus: int
{
    case DRAFT = 1; // Заказ создали

    case PENDING = 2; // Нажали на кнопку оплатить

    case DELETED = 3; // Удалили заказ

    case SUCCESS = 4;

    public function toHumane(): string
    {
        return match ($this) {
            self::DRAFT => 'Черновик',
            self::PENDING => 'Заказ на стадий оплаты',
            self::DELETED => 'Удален',
            self::SUCCESS => 'Завершен'
        };
    }
}
