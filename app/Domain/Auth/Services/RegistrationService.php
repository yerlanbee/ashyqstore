<?php
declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Dto\RegisterDto;
use App\Domain\Auth\Dto\SuccessLoginDto;
use App\Infrastructure\Models\User;
use App\Infrastructure\Support\Core\CustomException;

final class RegistrationService
{
    public function handle(RegisterDto $dto): SuccessLoginDto
    {
        $user = User::wherePhone($dto->phone);

        if ($user) {
            new CustomException('User already exists.');
        }

        $newUser = User::createNew($dto);

        return new SuccessLoginDto(
            $newUser->getKey(),
            $newUser->username,
            $newUser->createToken("AUTH TOKEN")->plainTextToken
        );
    }
}
