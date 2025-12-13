<?php
declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Dto\RegisterDto;
use App\Domain\Auth\Dto\SuccessRegisteredDto;
use App\Infrastructure\Models\User;
use App\Infrastructure\Support\Core\CustomException;

final class RegistrationService
{
    public function handle(RegisterDto $dto): SuccessRegisteredDto
    {
        $user = User::wherePhone($dto->phone);

        if ($user) {
            new CustomException('User already exists.');
        }

        $newUser = User::createNew($dto);

        return new SuccessRegisteredDto(
            $newUser->getKey(),
            $newUser->username,
            $user->createToken("AUTH TOKEN")->plainTextToken
        );
    }
}
