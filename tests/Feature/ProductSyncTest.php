<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Product\DTOs\ProductData;
use App\Domains\Product\Repositories\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_can_be_synced_from_dummy_json(): void
    {
        Http::fake([
            'https://dummyjson.com/products*' => Http::response([
                'products' => [
                    [
                        'id' => 11,
                        'title' => 'Test Product',
                        'price' => 12.99,
                        'stock' => 25,
                    ],
                    [
                        'id' => 12,
                        'title' => 'Second Product',
                        'price' => 19.99,
                        'stock' => 10,
                    ],
                ],
            ], 200),
        ]);

        $this->artisan('products:sync')
            ->assertSuccessful();

        $this->assertDatabaseHas('products', [
            'external_id' => 11,
            'title' => 'Test Product',
            'price' => 129900,
            'stock' => 25,
        ]);

        $this->assertDatabaseHas('products', [
            'external_id' => 12,
            'title' => 'Second Product',
            'price' => 199900,
            'stock' => 10,
        ]);

        $this->assertDatabaseCount('products', 2);
    }

    public function test_product_sync_updates_existing_product(): void
    {
        $productRepository = app(ProductRepositoryInterface::class);

        $productRepository->upsert(
            new ProductData(
                externalId: 11,
                title: 'Old Product',
                price: 10000,
                stock: 5,
            ) );

        Http::fake([
            'https://dummyjson.com/products*' => Http::response([
                'products' => [
                    [
                        'id' => 11,
                        'title' => 'Updated Product',
                        'price' => 15.50,
                        'stock' => 30,
                    ],
                ],
            ], 200),
        ]);

        $this->artisan('products:sync')
            ->assertSuccessful();

        $this->assertDatabaseHas('products', [
            'external_id' => 11,
            'title' => 'Updated Product',
            'price' => 155000,
            'stock' => 30,
        ]);

        $this->assertDatabaseCount('products', 1);
    }

    public function test_product_sync_fails_when_dummy_json_returns_error(): void
    {
        Http::fake([
            'https://dummyjson.com/products*' => Http::response([], 500),
        ]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        $this->artisan('products:sync'); $this->assertDatabaseCount('products', 0);
    }
}

