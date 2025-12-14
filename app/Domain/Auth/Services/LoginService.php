<?php
declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Dto\LoginDto;
use App\Domain\Auth\Dto\SuccessLoginDto;
use App\Infrastructure\Models\User;
use App\Infrastructure\Support\Core\CustomException;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

final class LoginService
{
    public function handle(LoginDto $dto): SuccessLoginDto
    {
        $user = User::wherePhone($dto->phone);

        if (! $user) {
            new CustomException('User not found');
        }

        if (! Hash::check($dto->password, $user->password))
        {
            new CustomException('Incorrect password', Response::HTTP_BAD_REQUEST, []);
        }

        $user?->tokens()->delete();

        return new SuccessLoginDto(
            $user->id,
            $user->username,
            $user?->createToken('authorization')->plainTextToken
        );
    }
}
