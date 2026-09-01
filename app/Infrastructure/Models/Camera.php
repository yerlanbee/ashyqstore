<?php

declare(strict_types=1);

namespace App\Infrastructure\Models;

use App\Infrastructure\Models\Contracts\CameraContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property ?int $fridge_id
 * @property string $name
 * @property string $serial
 * @property ?string $verification_code
 * @property int $channel_no
 * @property bool $is_active
 *
 * @property-read ?Fridge $fridge
 */
class Camera extends Model implements CameraContract
{
    protected $table = self::TABLE;

    protected $fillable = self::FIELDS;

    protected $casts = [
        'is_active' => 'bool',
        'channel_no' => 'int',
    ];

    public function fridge(): BelongsTo
    {
        return $this->belongsTo(Fridge::class);
    }

    public static function findById(int $id): ?Model
    {
        return self::query()->find($id);
    }

    public static function getAll(array $columns = ['*']): Collection
    {
        return self::query()->get($columns);
    }
}
