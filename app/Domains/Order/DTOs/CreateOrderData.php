<?php

declare(strict_types=1);

namespace App\Domains\Order\DTOs;

use App\Domains\Order\Enums\ShippingMethod;

final readonly class CreateOrderData
{
    public function __construct(
        public int $productId,
        public int $quantity,
        public ShippingMethod $shippingMethod,
    ) {}
}
