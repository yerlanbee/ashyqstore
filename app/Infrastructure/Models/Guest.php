<?php

namespace App\Infrastructure\Models;

use App\Domain\Order\Dto\GuestDto;
use App\Infrastructure\Models\Contracts\GuestContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $user_agent
 * @property string $ip_address
 * @property string $uuid
 *
 * @property-read Collection $orders
 */
class Guest extends Model implements GuestContract
{
    protected $fillable = self::FIELDS;

    protected $hidden = [
        'created_at',
        'updated_at',
        'ip_address',
    ];

    public static function whereUuid(string $uuid): ?self
    {
        return self::query()->where('uuid', $uuid)->first();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public static function createNew(GuestDto $attributes): ?Guest
    {
        return self::query()->create($attributes->toArray());
    }
}
