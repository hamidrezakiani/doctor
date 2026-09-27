<?php

namespace App\Providers;

use App\Domains\Order\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Domains\Order\Repositories\Contracts\OrderRepositoryInterface;
use App\Domains\Order\Repositories\Eloquent\EloquentOrderItemRepository;
use App\Domains\Order\Repositories\Eloquent\EloquentOrderRepository;
use App\Domains\Order\Strategies\PickupShippingStrategy;
use App\Domains\Order\Strategies\PostShippingStrategy;
use App\Domains\Order\Strategies\ShippingCostResolver;
use App\Domains\Order\Strategies\TipaxShippingStrategy;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ShippingCostResolver::class, function (): ShippingCostResolver {
            return new ShippingCostResolver([
                $this->app->make(PostShippingStrategy::class),
                $this->app->make(TipaxShippingStrategy::class),
                $this->app->make(PickupShippingStrategy::class),
            ]);
        });

        $this->app->bind(
            OrderRepositoryInterface::class,
            EloquentOrderRepository::class,
        );

        $this->app->bind(
            OrderItemRepositoryInterface::class,
            EloquentOrderItemRepository::class,
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
