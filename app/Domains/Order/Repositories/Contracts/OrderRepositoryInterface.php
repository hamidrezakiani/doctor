<?php

declare(strict_types=1);

namespace App\Domains\Order\Repositories\Contracts;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Enums\ShippingMethod;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    public function create(
        int $subtotal,
        int $shippingCost,
        int $total,
        ShippingMethod $shippingMethod,
    ): Order;

    public function updateStatus(
        Order $order,
        OrderStatus $status,
    ): Order;

    public function find(int $id): ?Order;

    public function paginate(
        ?OrderStatus $status = null,
        int $perPage = 15,
    ): LengthAwarePaginator;

    public function findShippedBefore(
        CarbonImmutable $date,
    ): Collection;
}
