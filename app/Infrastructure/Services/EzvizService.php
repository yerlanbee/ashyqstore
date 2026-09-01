<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Infrastructure\Services\Contracts\EzvizServiceContract;
use DateTimeInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Клиент Ezviz Open Platform API.
 *
 * Документация: https://open.ezviz.com/uikit
 */
class EzvizService implements EzvizServiceContract
{
    private const TIMEOUT = 30;

    private const TOKEN_TTL = 6 * 24 * 3600; // 6 дней (токен живёт 7, обновляем с запасом)

    private const CACHE_KEY = 'ezviz:access_token';

    /** HLS-протокол для веб-плеера. 1=ezopen, 2=HLS, 3=RTMP, 4=FLV */
    private const PROTOCOL_HLS = 2;

    private const TYPE_LIVE = 1;

    private const TYPE_PLAYBACK_CLOUD = 2;

    private readonly string $appKey;

    private readonly string $appSecret;

    private readonly string $baseUrl;

    public function __construct()
    {
        $this->appKey = (string) config('services.ezviz.app_key');
        $this->appSecret = (string) config('services.ezviz.app_secret');
        $this->baseUrl = rtrim((string) config('services.ezviz.url'), '/');

        if ($this->appKey === '' || $this->appSecret === '') {
            throw new RuntimeException('Ezviz credentials not configured (services.ezviz.app_key / app_secret).');
        }
    }

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    public function getAccessToken(): string
    {
        $cached = Cache::get(self::CACHE_KEY);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $response = Http::timeout(self::TIMEOUT)
            ->asForm()
            ->acceptJson()
            ->baseUrl($this->baseUrl)
            ->post('/api/lapp/token/get', [
                'appKey' => $this->appKey,
                'appSecret' => $this->appSecret,
            ]);

        $response->throw();

        $data = (array) $response->json('data', []);
        $token = (string) ($data['accessToken'] ?? '');

        if ($token === '') {
            throw new ConnectionException('Ezviz token endpoint returned empty token: ' . $response->body());
        }

        Cache::put(self::CACHE_KEY, $token, self::TOKEN_TTL);

        return $token;
    }

    /**
     * @return array{url: string, expireTime: ?string}
     * @throws ConnectionException
     * @throws RequestException
     */
    public function getLiveAddress(string $serial, int $channelNo, ?string $verificationCode = null): array
    {
        return $this->requestAddress([
            'deviceSerial' => $serial,
            'channelNo' => $channelNo,
            'protocol' => self::PROTOCOL_HLS,
            'type' => self::TYPE_LIVE,
            'code' => $this->normalizeCode($verificationCode),
            'expireTime' => 3600,
        ]);
    }

    /**
     * @return array{url: string, expireTime: ?string}
     * @throws ConnectionException
     * @throws RequestException
     */
    public function getPlaybackAddress(
        string $serial,
        int $channelNo,
        DateTimeInterface $from,
        DateTimeInterface $to,
        ?string $verificationCode = null,
    ): array {
        return $this->requestAddress([
            'deviceSerial' => $serial,
            'channelNo' => $channelNo,
            'protocol' => self::PROTOCOL_HLS,
            'type' => self::TYPE_PLAYBACK_CLOUD,
            'code' => $this->normalizeCode($verificationCode),
            'startTime' => $from->getTimestamp() * 1000,
            'stopTime' => $to->getTimestamp() * 1000,
            'expireTime' => 3600,
        ]);
    }

    private function normalizeCode(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        $trimmed = strtoupper(trim($code));

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    public function isEncryptionEnabled(string $serial): bool
    {
        $response = $this->send(fn (PendingRequest $http) => $http->post(
            '/api/lapp/device/info',
            ['deviceSerial' => $serial],
        ));

        $data = (array) $response->json('data', []);

        return (int) ($data['isEncrypt'] ?? 0) === 1;
    }

    /**
     * @return array{url: string, expireTime: ?string}
     * @throws ConnectionException
     * @throws RequestException
     */
    private function requestAddress(array $params): array
    {
        $payload = array_filter($params, static fn ($v) => $v !== null && $v !== '');

        Log::info('[ezviz] live/address/get payload', $payload);

        $response = $this->send(fn (PendingRequest $http) => $http->post(
            '/api/lapp/v2/live/address/get',
            $payload,
        ));

        Log::info('[ezviz] live/address/get response', (array) $response->json());

        $data = (array) $response->json('data', []);

        $url = (string) ($data['url'] ?? '');

        if ($url === '') {
            $code = $response->json('code');
            $msg = $response->json('msg') ?? $response->body();
            throw new RuntimeException("Ezviz returned no URL (code={$code}): {$msg}");
        }

        return [
            'url' => $url,
            'expireTime' => isset($data['expireTime']) ? (string) $data['expireTime'] : null,
        ];
    }

    /**
     * Один retry с очисткой токена, если API ответил кодом «токен истёк».
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    private function send(callable $callback): Response
    {
        $response = $callback($this->newRequest());

        if ($this->isTokenExpired($response)) {
            Cache::forget(self::CACHE_KEY);
            $response = $callback($this->newRequest());
        }

        $response->throw();

        return $response;
    }

    private function newRequest(): PendingRequest
    {
        $token = $this->getAccessToken();

        return Http::timeout(self::TIMEOUT)
            ->asForm()
            ->acceptJson()
            ->baseUrl($this->baseUrl)
            ->withOptions([
                'query' => ['accessToken' => $token],
            ]);
    }

    /**
     * Коды Ezviz: 10002 — accessToken expired, 10001 — invalid params.
     */
    private function isTokenExpired(Response $response): bool
    {
        if ($response->status() === 401) {
            return true;
        }

        $code = (string) ($response->json('code') ?? '');

        return in_array($code, ['10002', '10004'], true);
    }
}
