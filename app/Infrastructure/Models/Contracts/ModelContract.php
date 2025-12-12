<?php

namespace App\Infrastructure\Models\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ModelContract
{
    // TODO временное решение вместо Repository. Contract который хранит в себе CRUD.
    public static function findById(int $id): ?Model;

    public static function getAll(array $columns = ['*']): Collection;
}
