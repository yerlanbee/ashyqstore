<?php

namespace App\Infrastructure\Models;

use App\Orchid\Presenters\UserPresenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Orchid\Access\UserAccess;
use Orchid\Platform\Models\User as OrchidUser;

/**
 * @property int $id
 * @property ?string $name
 * @property string $email
 * @property string $password
 */
class User extends OrchidUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, UserAccess;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
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

    public function presenter(): UserPresenter
    {
        return new UserPresenter($this);
    }
}
