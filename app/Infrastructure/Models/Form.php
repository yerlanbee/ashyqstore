<?php

namespace App\Infrastructure\Models;

use App\Infrastructure\Models\Contracts\FridgeContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $full_name
 * @property string $phone
 */
class Form extends Model
{
    protected $table = 'forms';

    protected $fillable = [
        'full_name',
        'phone',
    ];
}
