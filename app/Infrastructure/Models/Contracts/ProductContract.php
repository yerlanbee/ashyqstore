<?php
declare(strict_types=1);

namespace App\Infrastructure\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

interface ProductContract extends ModelContract
{
    const FIELD_NAME = 'name';
    const FIELD_SLUG = 'slug';
    const FIELD_IMAGE = 'image';

    const FIELD_QUANTITY = 'quantity';
    const FIELD_SORT = 'sort';
    const FIELD_IS_VISIBLE = 'is_visible';

    const FIELD_PRICE = 'price';

    const TABLE = 'products';

    const FIELDS = [
        self::FIELD_NAME,
        self::FIELD_SLUG,
        self::FIELD_IMAGE,
        self::FIELD_QUANTITY,
        self::FIELD_SORT,
        self::FIELD_IS_VISIBLE,
        self::FIELD_PRICE,
    ];

    public function category(): HasOne;

    public function orders(): BelongsToMany;
}
