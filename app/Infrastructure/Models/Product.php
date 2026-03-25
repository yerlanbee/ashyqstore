<?php

namespace App\Infrastructure\Models;

use App\Infrastructure\Models\Contracts\ProductContract;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


/**
 * @property int $id
 * @property string $name
 * @property string $uuid
 * @property string $image
 * @property int $quantity
 * @property float $price
 * @property int $code
 * @property bool $is_visible
 * @property int $sort
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Category $category
 * @property-read Fridge $fridge
 */
class Product extends Model implements ProductContract
{

    protected $table = self::TABLE;

    protected $fillable = self::FIELDS;

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function fridge(): BelongsTo
    {
        return $this->belongsTo(Fridge::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class)->withPivot(['quantity'])
            ->withTimestamps();
    }

    public static function findById(int $id): ?Product
    {
        return self::query()->find($id);
    }

    public static function getAll(array $columns = ['*']): Collection
    {
        return self::query()->get();
    }

    public static function whereVisible(?int $id = null, array $cols = ['*']): Collection
    {
        return self::query()->where('is_visible', true)
            ->when($id, function ($query) use ($id) {
                return $query->where('id', $id);
            })->get($cols);
    }

    public static function getByIds(array $ids, array $columns = ['*']): Collection
    {
        return self::query()->whereIn('id', $ids)->get($columns);
    }
}
