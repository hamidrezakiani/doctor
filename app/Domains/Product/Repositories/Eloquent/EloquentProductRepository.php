<?php

declare(strict_types=1);

namespace App\Domains\Product\Repositories\Eloquent;

use App\Domains\Product\DTOs\ProductData;
use App\Domains\Product\Repositories\Contracts\ProductRepositoryInterface;
use App\Models\Product;

final class EloquentProductRepository implements ProductRepositoryInterface
{
    public function upsert(ProductData $product): Product
    {
        return Product::query()->updateOrCreate(
            [
                'external_id' => $product->externalId,
            ],
            [
                'title' => $product->title,
                'price' => $product->price,
                'stock' => $product->stock,
            ],
        );
    }
}
