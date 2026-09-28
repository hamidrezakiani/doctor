<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Repositories\Contracts\OrderRepositoryInterface;
use Carbon\CarbonImmutable;

final readonly class DeliverExpiredOrdersAction
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {}

    public function execute(): int
    {
        $orders = $this->orderRepository->findShippedBefore(
            CarbonImmutable::now()->subHours(24),
        );

        foreach ($orders as $order) {
            $this->orderRepository->updateStatus(
                $order,
                OrderStatus::DELIVERED,
            );
        }

        return $orders->count();
    }
}
