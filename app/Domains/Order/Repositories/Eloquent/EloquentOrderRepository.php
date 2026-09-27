<?php

declare(strict_types=1);

namespace App\Domains\Order\Repositories\Eloquent;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Enums\ShippingMethod;
use App\Domains\Order\Repositories\Contracts\OrderRepositoryInterface;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

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
        $order->status = $status;

        if ($status === OrderStatus::SHIPPED) {
            $order->shipped_at = now();
        }

        $order->save();

        return $order->refresh();
    }

    public function find(int $id): ?Order
    {
        return Order::query()->find($id);
    }

    public function paginate(
        ?OrderStatus $status = null,
        int $perPage = 15,
    ): LengthAwarePaginator {
        $query = Order::query()
            ->latest('id');

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    public function findShippedBefore(
        CarbonImmutable $date,
    ): Collection {
        return Order::query()
            ->where('status', OrderStatus::SHIPPED)
            ->where('shipped_at', '<=', $date)
            ->get();
    }
}
