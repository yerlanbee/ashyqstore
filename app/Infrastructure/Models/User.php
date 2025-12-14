<?php

namespace App\Infrastructure\Models;

use App\Domain\Auth\Dto\RegisterDto;
use App\Domain\Shared\ValueObject\PhoneVO;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $username
 * @property string $phone
 * @property string $password
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static function wherePhone(PhoneVO $phone): ?User
    {
        return self::query()->where('phone', $phone->value())->first();
    }

    public static function createNew(RegisterDto $dto): User
    {
        return self::query()->create([
            'phone' => $dto->phone->value(),
            'username' => $dto->username,
            'password' => $dto->getPassword(),
        ]);
    }
}
