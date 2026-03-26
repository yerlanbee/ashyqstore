<?php
declare(strict_types=1);

namespace App\Infrastructure\Services\Contracts;

use Illuminate\Http\Client\PendingRequest;

interface BusinessClodServiceContract
{
    public function newRequest(): PendingRequest;

    public function getJWT(): string;

    public function getTransactions(array $filters): array;
}
