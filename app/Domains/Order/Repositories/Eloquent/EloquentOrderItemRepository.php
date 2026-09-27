<?php

declare(strict_types=1);

namespace App\Domains\Order\Repositories\Eloquent;

use App\Domains\Order\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Models\Order;
use App\Models\OrderItem;

final class EloquentOrderItemRepository implements OrderItemRepositoryInterface
{
    public function create(
        Order $order,
        int $productId,
        int $quantity,
        int $unitPrice,
        int $total,
    ): OrderItem {
        return $order->items()->create([
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $total,
        ]);
    }
}
