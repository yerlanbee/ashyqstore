<?php

namespace App\Infrastructure\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface OrderContract
{
    const TABLE = "orders";

    const FIELD_STATUS = 'status';
    const FIELD_TOTAL_PRICE = 'total_price';

    const FIELD_GUEST_ID = 'guest_id';

    const FIELDS = [
        self::FIELD_STATUS,
        self::FIELD_TOTAL_PRICE,
        self::FIELD_GUEST_ID
    ];

    public function products(): BelongsToMany;

    public function guest(): BelongsTo;
}
