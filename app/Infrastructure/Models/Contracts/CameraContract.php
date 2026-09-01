<?php

declare(strict_types=1);

namespace App\Infrastructure\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface CameraContract extends ModelContract
{
    const FIELD_NAME = 'name';
    const FIELD_SERIAL = 'serial';
    const FIELD_VERIFICATION_CODE = 'verification_code';
    const FIELD_CHANNEL_NO = 'channel_no';
    const FIELD_IS_ACTIVE = 'is_active';
    const FIELD_FRIDGE_ID = 'fridge_id';

    const TABLE = 'cameras';

    const FIELDS = [
        self::FIELD_NAME,
        self::FIELD_SERIAL,
        self::FIELD_VERIFICATION_CODE,
        self::FIELD_CHANNEL_NO,
        self::FIELD_IS_ACTIVE,
        self::FIELD_FRIDGE_ID,
    ];

    public function fridge(): BelongsTo;
}
