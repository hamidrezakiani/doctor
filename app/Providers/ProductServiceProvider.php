<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Product\Repositories\Contracts\ProductRepositoryInterface;
use App\Domains\Product\Repositories\Eloquent\EloquentProductRepository;
use Illuminate\Support\ServiceProvider;

class ProductServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            ProductRepositoryInterface::class,
            EloquentProductRepository::class,
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
