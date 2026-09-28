<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Exceptions\InvalidOrderStatusTransitionException;
use App\Domains\Order\Repositories\Contracts\OrderRepositoryInterface;
use App\Models\Order;

final readonly class ShipOrderAction
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {}

    public function execute(Order $order): Order
    {
        if ($order->status !== OrderStatus::PAID) {
            throw new InvalidOrderStatusTransitionException;
        }

        return $this->orderRepository->updateStatus(
            $order,
            OrderStatus::SHIPPED,
        );
    }
}
