<?php
declare(strict_types=1);

namespace App\Infrastructure\Support\Core;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * @return array
     */
    abstract public function rules(): array;

    /**
     * @return bool
     */
    abstract public function authorize(): bool;

    /**
     * @param Validator $validator
     * @return void
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message'   => $validator->errors()->first(),
            'data'      => []
        ]));
    }

    abstract public function toDto();
}
