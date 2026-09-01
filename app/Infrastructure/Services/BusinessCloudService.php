<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Infrastructure\Services\Contracts\BusinessClodServiceContract;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BusinessCloudService implements BusinessClodServiceContract
{
    private const TIMEOUT = 120;

    private const TOKEN_TTL = 3600;

    private const CACHE_KEY = 'business_cloud:jwt';

    /** Жёсткий лимит API: pageSize вне 1..100 отдаёт 422. */
    public const MAX_PAGE_SIZE = 100;

    private readonly string $email;

    private readonly string $password;

    private readonly string $merchantId;

    private readonly string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = (string) config('services.business_cloud.url');
        $this->email = (string) config('services.business_cloud.email');
        $this->password = (string) config('services.business_cloud.password');
        $this->merchantId = (string) config('services.business_cloud.merchant_id');
    }

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    public function getTransactions(array $filters): array
    {
        $response = $this->send(fn (PendingRequest $http) => $http->post('api/transactions', $filters));

        return $response->json() ?? [];
    }

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    public function getProducts(): array
    {
        $response = $this->send(fn (PendingRequest $http) => $http->get('api/products'));

        return $response->json('items') ?? [];
    }

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    public function getJWT(): string
    {
        $cached = Cache::get(self::CACHE_KEY);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $response = Http::timeout(self::TIMEOUT)
            ->acceptJson()
            ->baseUrl($this->baseUrl)
            ->post('api/auth/login', [
                'userName' => $this->email,
                'password' => $this->password,
            ]);

        $response->throw();

        $token = (string) ($response->json('access_token') ?? '');

        if ($token === '') {
            throw new ConnectionException('Business Cloud login returned empty token');
        }

        Cache::put(self::CACHE_KEY, $token, self::TOKEN_TTL);

        return $token;
    }

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    public function newRequest(): PendingRequest
    {
        return Http::timeout(self::TIMEOUT)
            ->withToken($this->getJWT())
            ->withHeaders([
                'X-Merchant-Id' => $this->merchantId,
            ])
            ->acceptJson()
            ->baseUrl($this->baseUrl);
    }

    /**
     * Выполнить запрос с одним повтором при истечении токена (401).
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    private function send(callable $callback): Response
    {
        $response = $callback($this->newRequest());

        if ($response->status() === 401) {
            Cache::forget(self::CACHE_KEY);
            $response = $callback($this->newRequest());
        }

        $response->throw();

        return $response;
    }
}
