<?php
declare(strict_types=1);

namespace App\Infrastructure\Services\Contracts;

interface BusinessClodServiceContract
{
    public function login(): array;

    public function getItems(): array;
}
