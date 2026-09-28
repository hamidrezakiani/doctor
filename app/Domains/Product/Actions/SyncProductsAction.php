<?php

declare(strict_types=1);

namespace App\Domains\Product\Actions;

use App\Domains\Product\DTOs\ProductData;
use App\Domains\Product\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

final class SyncProductsAction
{
    private const string PRODUCTS_URL = 'https://dummyjson.com/products';

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function execute(): int
    {
        $response = Http::get(self::PRODUCTS_URL, [
            'limit' => 10,
            'skip' => 10,
            'select' => 'title,price,stock',
        ]);

        $response->throw();

        $products = $response->json('products', []);

        foreach ($products as $product) {
            $productData = ProductData::fromApi($product);

            $this->productRepository->upsert($productData);
        }

        return count($products);
    }
}
