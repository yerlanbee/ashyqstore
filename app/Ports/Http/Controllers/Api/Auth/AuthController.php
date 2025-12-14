<?php

namespace App\Ports\Http\Controllers\Api\Auth;

use App\Domain\Auth\Services\LoginService;
use App\Domain\Auth\Services\RegistrationService;
use App\Ports\Http\Controllers\Controller;
use App\Ports\Http\Requests\Auth\LoginValidation;
use App\Ports\Http\Requests\Auth\RegisterRequestValidation;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function register(RegisterRequestValidation $validation, RegistrationService $service): JsonResponse
    {
        $registered = $service->handle($validation->toDto());

        return $this->response(
            data: $registered->toArray(),
        );
    }

    public function login(LoginValidation $validation, LoginService $service): JsonResponse
    {
        return $this->response(
            data: $service->handle($validation->toDto())
        );
    }
}
