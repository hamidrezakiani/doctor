<?php

declare(strict_types=1);

namespace App\Domains\Product\Repositories\Contracts;

use App\Domains\Product\DTOs\ProductData;
use App\Models\Product;

interface ProductRepositoryInterface
{
    public function upsert(ProductData $product): Product;
}
