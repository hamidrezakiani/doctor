<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\DTOs\CreateOrderData;
use App\Domains\Order\DTOs\OrderData;
use App\Domains\Order\Exceptions\InsufficientStockException;
use App\Domains\Order\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Domains\Order\Repositories\Contracts\OrderRepositoryInterface;
use App\Domains\Order\Strategies\ShippingCostResolver;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateOrderAction
{
    public function __construct(
        private OrderRepositoryInterface     $orderRepository,
        private OrderItemRepositoryInterface $orderItemRepository,
        private ShippingCostResolver         $shippingCostResolver,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function execute(CreateOrderData $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $product = Product::query()
                ->findOrFail($data->productId);

            $stockUpdated = Product::query()
                ->whereKey($data->productId)
                ->where('stock', '>=', $data->quantity)
                ->decrement('stock', $data->quantity);

            if ($stockUpdated === 0) {
                throw new InsufficientStockException();
            }

            $subtotal = $product->price * $data->quantity;

            $shippingCost = $this->shippingCostResolver->calculate(
                $data->shippingMethod,
            );

            $total = $subtotal + $shippingCost;

            $order = $this->orderRepository->create(
                subtotal: $subtotal,
                shippingCost: $shippingCost,
                total: $total,
                shippingMethod: $data->shippingMethod,
            );

            $this->orderItemRepository->create(
                order: $order,
                productId: $product->id,
                quantity: $data->quantity,
                unitPrice: $product->price,
                total: $subtotal,
            );

            return $order;
        });
    }
}
