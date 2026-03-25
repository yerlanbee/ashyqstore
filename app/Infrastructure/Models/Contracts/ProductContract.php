<?php
declare(strict_types=1);

namespace App\Infrastructure\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

interface ProductContract extends ModelContract
{
    const FIELD_NAME = 'name';
    const FIELD_UUID = 'uuid';
    const FIELD_IMAGE = 'image';

    const FIELD_QUANTITY = 'quantity';
    const FIELD_SORT = 'sort';
    const FIELD_IS_VISIBLE = 'is_visible';

    const FIELD_PRICE = 'price';

    const FIELD_CATEGORY_ID = 'category_id';

    const FIELD_FRIDGE_ID = 'fridge_id';

    const FIELD_CODE = 'code';
    const TABLE = 'products';

    const FIELDS = [
        self::FIELD_NAME,
        self::FIELD_UUID,
        self::FIELD_IMAGE,
        self::FIELD_QUANTITY,
        self::FIELD_SORT,
        self::FIELD_IS_VISIBLE,
        self::FIELD_PRICE,
        self::FIELD_CATEGORY_ID,
        self::FIELD_FRIDGE_ID,
        self::FIELD_CODE,
    ];

    public function category(): BelongsTo;
    public function fridge(): BelongsTo;

    public function orders(): BelongsToMany;
}
