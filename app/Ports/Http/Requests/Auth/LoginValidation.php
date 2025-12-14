<?php

namespace App\Ports\Http\Requests\Auth;

use App\Domain\Auth\Dto\LoginDto;
use App\Domain\Shared\ValueObject\PhoneVO;
use App\Infrastructure\Support\Core\BaseFormRequest;

class LoginValidation extends BaseFormRequest
{

    public function rules(): array
    {
        return [
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

    public function toDto(): LoginDto
    {
        $validated = $this->validated();

        return new LoginDto(
            PhoneVO::from($validated['phone']),
            $validated['password'],
        );
    }
}
