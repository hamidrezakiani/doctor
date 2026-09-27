<?php

declare(strict_types=1);

namespace App\Domains\Order\Repositories\Eloquent;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Enums\ShippingMethod;
use App\Domains\Order\Repositories\Contracts\OrderRepositoryInterface;
use App\Models\Order;

final class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function create(
        int $subtotal,
        int $shippingCost,
        int $total,
        ShippingMethod $shippingMethod,
    ): Order {
        return Order::query()->create([
            'status' => OrderStatus::PAID,
            'shipping_method' => $shippingMethod,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'total' => $total,
        ]);
    }

    public function updateStatus(
        Order $order,
        OrderStatus $status,
    ): Order {
        $order->update([
            'status' => $status,
        ]);

        return $order->refresh();
    }

    public function find(int $id): ?Order
    {
        return Order::query()->find($id);
    }
}
