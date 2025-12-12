<?php
declare(strict_types=1);

namespace App\Infrastructure\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface CategoryContract extends ModelContract
{
    const FIELD_NAME = 'name';
    const FIELD_SLUG = 'slug';
    const FIELD_IMAGE = 'image';
    const FIELD_IS_VISIBLE = 'is_visible';
    const FIELD_SORT = 'sort';

    const TABLE = 'categories';

    const FIELDS = [
        self::FIELD_NAME,
        self::FIELD_SLUG,
        self::FIELD_IMAGE,
        self::FIELD_IS_VISIBLE,
        self::FIELD_SORT,
    ];

    public function products(): HasMany;
}
