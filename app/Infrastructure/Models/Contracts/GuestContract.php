<?php

namespace App\Infrastructure\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface GuestContract
{
    const TABLE = 'guests';

    const FIELD_USER_AGENT = 'user_agent';
    const FIELD_IP_ADDRESS = 'ip_address';
    const FIELD_UUID = 'uuid';

    const FIELDS = [
        self::FIELD_USER_AGENT,
        self::FIELD_IP_ADDRESS,
        self::FIELD_UUID,
    ];

    public function orders(): HasMany;
}
