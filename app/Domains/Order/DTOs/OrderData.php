<?php

declare(strict_types=1);

namespace App\Domains\Order\DTOs;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Enums\ShippingMethod;
use App\Models\Order;

final readonly class OrderData
{
    public function __construct(
        public int $id,
        public OrderStatus $status,
        public ShippingMethod $shippingMethod,
        public int $subtotal,
        public int $shippingCost,
        public int $total,
    ) {
    }

    public static function fromModel(Order $order): self
    {
        return new self(
            id: $order->id,
            status: $order->status,
            shippingMethod: $order->shipping_method,
            subtotal: $order->subtotal,
            shippingCost: $order->shipping_cost,
            total: $order->total,
        );
    }
}
