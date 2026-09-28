<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Enums\ShippingMethod;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class OrderConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    //    public function test_only_one_concurrent_request_can_consume_last_stock(): void
    //    {
    //        $product = Product::query()->create([
    //            'external_id' => 999,
    //            'title' => 'Concurrency Product',
    //            'price' => 100_000,
    //            'stock' => 1,
    //        ]);
    //
    //        $payload = [
    //            'product_id' => $product->id,
    //            'quantity' => 1,
    //            'shipping_method' => ShippingMethod::PICKUP->value,
    //        ];
    //
    //        $responses = Http::pool(fn ($pool) => [
    //            $pool->as('first')->post(
    //                'http://nginx/api/orders',
    //                $payload,
    //            ),
    //            $pool->as('second')->post(
    //                'http://nginx/api/orders',
    //                $payload,
    //            ),
    //        ]);
    //
    //        $statuses = [
    //            $responses['first']->status(),
    //            $responses['second']->status(),
    //        ];
    //
    //        sort($statuses);
    //
    //
    //        $this->assertSame(
    //            [201, 409],
    //            $statuses,
    //        );
    //
    //        $this->assertDatabaseHas('products', [
    //            'id' => $product->id,
    //            'stock' => 0,
    //        ]);
    //
    //        $this->assertDatabaseCount('orders', 1);
    //
    //        $this->assertDatabaseHas('orders', [
    //            'status' => OrderStatus::PAID->value,
    //            'shipping_method' => ShippingMethod::PICKUP->value,
    //        ]);
    //    }
}
