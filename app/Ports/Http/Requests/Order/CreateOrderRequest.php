<?php

namespace App\Ports\Http\Requests\Order;

use App\Domain\Order\Dto\OrderDto;
use App\Infrastructure\Support\Core\BaseFormRequest;

class CreateOrderRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'guest' => ['required', 'array'],
            'guest.user_agent' => ['required', 'string'],
            'guest.ip_address' => ['required', 'string'],
            'guest.uuid' => ['required', 'uuid'],
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'integer', ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function toDto(): OrderDto
    {
        $validated = $this->validated();

        return OrderDto::fromArray($validated);
    }
}
