<?php

declare(strict_types=1);

namespace App\Infrastructure\Services\Contracts;

interface EzvizServiceContract
{
    public function getAccessToken(): string;

    /**
     * @return array{url: string, expireTime: ?string}
     */
    public function getLiveAddress(string $serial, int $channelNo, ?string $verificationCode = null): array;

    /**
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @return array{url: string, expireTime: ?string}
     */
    public function getPlaybackAddress(
        string $serial,
        int $channelNo,
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        ?string $verificationCode = null,
    ): array;

    public function isEncryptionEnabled(string $serial): bool;
}
