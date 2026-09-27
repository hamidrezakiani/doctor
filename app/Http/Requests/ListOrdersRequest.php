<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domains\Order\Enums\OrderStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListOrdersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'nullable',
                Rule::enum(OrderStatus::class),
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }

    public function status(): ?OrderStatus
    {
        $status = $this->input('status');

        return $status !== null
            ? OrderStatus::from($status)
            : null;
    }

    public function perPage(): int
    {
        return $this->integer('per_page', 15);
    }
}
