<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class ListOrdersAction
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function execute(
        ?OrderStatus $status = null,
        int $perPage = 15,
    ): LengthAwarePaginator {
        return $this->orderRepository->paginate(
            status: $status,
            perPage: $perPage,
        );
    }
}
