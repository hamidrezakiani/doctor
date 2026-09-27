<?php

declare(strict_types=1);

namespace App\Domains\Order\Repositories\Contracts;

use App\Models\Order;
use App\Models\OrderItem;

interface OrderItemRepositoryInterface
{
    public function create(
        Order $order,
        int $productId,
        int $quantity,
        int $unitPrice,
        int $total,
    ): OrderItem;
}
