<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domains\Order\DTOs\CreateOrderData;
use App\Domains\Order\Enums\ShippingMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreOrderRequest extends FormRequest
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
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'shipping_method' => [
                'required',
                Rule::enum(ShippingMethod::class),
            ],
        ];
    }

    public function toDto(): CreateOrderData
    {
        return new CreateOrderData(
            productId: (int) $this->integer('product_id'),
            quantity: (int) $this->integer('quantity'),
            shippingMethod: ShippingMethod::from(
                $this->string('shipping_method')->toString(),
            ),
        );
    }
}
