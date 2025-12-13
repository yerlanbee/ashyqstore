<?php

namespace App\Ports\Http\Requests\Auth;

use App\Domain\Auth\Dto\RegisterDto;
use App\Domain\Shared\ValueObject\PhoneVO;
use App\Infrastructure\Support\Core\BaseFormRequest;

class RegisterRequestValidation extends BaseFormRequest
{

    public function rules(): array
    {
        return [
            'username' => 'required|string|unique:users,username',
            'phone'   => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'password'  => [
                'required',
                'string',
                'min:8',
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function toDto(): RegisterDto
    {
        $validated = $this->validated();
        return new RegisterDto(
            $validated['username'],
            PhoneVO::from($validated['phone']),
            $validated['password'],
        );
    }
}
