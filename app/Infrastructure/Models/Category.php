<?php
declare(strict_types=1);

namespace App\Infrastructure\Models;

use App\Infrastructure\Models\Contracts\CategoryContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property string $slug
 * @property bool $is_visible
 * @property int $sort
 * @property string $image
 *
 * @property-read Collection $products
 */
class Category extends Model implements CategoryContract
{
    protected $table = self::TABLE;

    protected $fillable = self::FIELDS;

    public $hidden = [
        'created_at',
        'updated_at',
    ];

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

    public static function findWithProducts(int $id): ?Category
    {
        return self::query()->with('products')->find($id);
    }
}
