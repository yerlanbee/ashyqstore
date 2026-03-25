<?php
declare(strict_types=1);

namespace App\Infrastructure\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface FridgeContract extends ModelContract
{
    const FIELD_NAME = 'name';
    const FIELD_UUID = 'uuid';
    const FIELD_IS_VISIBLE = 'is_active';

    const TABLE = 'fridges';

    const FIELDS = [
        self::FIELD_NAME,
        self::FIELD_UUID,
        self::FIELD_IS_VISIBLE,
    ];

    public function products(): HasMany;
}
