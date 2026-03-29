<?php

namespace App\Infrastructure\Models;

use App\Infrastructure\Models\Contracts\FridgeContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $uuid
 * @property bool $is_active
 */
class Fridge extends Model implements FridgeContract
{
    protected $table = self::TABLE;

    protected $fillable = self::FIELDS;

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public static function findById(int $id): ?Model
    {
        return self::query()->find($id);
    }

    public static function getAll(array $columns = ['*']): Collection
    {
        return self::query()->get($columns);
    }

    public static function whereCode(string $code): Builder
    {
        return self::query()->where('code', $code);
    }
}
