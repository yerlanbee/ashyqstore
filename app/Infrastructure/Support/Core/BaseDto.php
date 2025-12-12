<?php
declare(strict_types=1);

namespace App\Infrastructure\Support\Core;

abstract class BaseDto
{
    abstract public static function fromArray(array $data);

    abstract public function toArray(): array;
}
