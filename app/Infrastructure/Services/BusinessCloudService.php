<?php

namespace App\Infrastructure\Services;

use App\Infrastructure\Services\Contracts\BusinessClodServiceContract;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BusinessCloudService implements BusinessClodServiceContract
{
    const TIMEOUT = 120;

    private string $email;

    private string $password;

    private string $merchantId;

    private string $baseUrl;

    private string $cacheKey = 'business_cloud_cache';

    public function __construct()
    {
        $this->baseUrl = env('BUSINESS_CLOUD_URL');
        $this->email = config('services.business_cloud.email');
        $this->password = config('services.business_cloud.password');
        $this->merchantId = config('services.business_cloud.merchant_id');
    }

    /**
     * @param array $filters
     * @return array
     * @throws ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getTransactions(array $filters): array
    {
//        dd($filters);
        $http = $this->newRequest()->post('api/transactions', $filters);

        $http->throw();

        return $http->json();
    }

    /**
     * @throws ConnectionException
     */
    public function getJWT(): string
    {
        $http = Http::baseUrl($this->baseUrl)->post('api/auth/login', [
            'userName' => $this->email,
            'password' => $this->password
        ]);

        if (! $http->successful())
        {
            throw new ConnectionException('Can not connect to business cloud');
        }

        return Cache::remember($this->cacheKey, 3600, function () use ($http) {
            return $http->json()['access_token'];
        });
    }

    /**
     * @throws ConnectionException
     */
    public function newRequest(): PendingRequest
    {
        return Http::timeout(self::TIMEOUT)
            ->withHeaders([
                'Authorization' => 'Bearer '.$this->getJWT(),
                'X-Merchant-Id' => $this->merchantId,
            ])->acceptJson()->baseUrl($this->baseUrl);
    }
}
