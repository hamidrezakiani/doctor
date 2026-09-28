<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Enums\ShippingMethod;
use App\Domains\Order\Repositories\Contracts\OrderRepositoryInterface;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_can_be_created(): void
    {
        $product = Product::query()->create([
            'external_id' => 100,
            'title' => 'Test Product',
            'price' => 100_000,
            'stock' => 10,
        ]);

        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 2,
            'shipping_method' => ShippingMethod::POST->value,
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'shipping_method',
                    'subtotal',
                    'shipping_cost',
                    'total',
                    'created_at',
                ],
            ]);

        $orderId = $response->json('data.id');

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => OrderStatus::PAID->value,
            'shipping_method' => ShippingMethod::POST->value,
            'subtotal' => 200_000,
            'shipping_cost' => 30_000,
            'total' => 230_000,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $orderId,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 100_000,
            'total' => 200_000,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 8,
        ]);
    }

    public function test_order_cannot_be_created_when_stock_is_insufficient(): void
    {
        $product = Product::query()->create([
            'external_id' => 101,
            'title' => 'Limited Product',
            'price' => 100_000,
            'stock' => 2,
        ]);

        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 3,
            'shipping_method' => ShippingMethod::POST->value,
        ]);

        $response
            ->assertStatus(409)
            ->assertJsonStructure([
                'message',
            ]);

        $this->assertDatabaseMissing('orders', [
            'shipping_method' => ShippingMethod::POST->value,
            'subtotal' => 300_000,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 2,
        ]);
    }

    public function test_only_one_order_can_consume_the_last_stock(): void
    {
        $product = Product::query()->create([
            'external_id' => 102,
            'title' => 'Last Stock Product',
            'price' => 100_000,
            'stock' => 1,
        ]);

        $firstResponse = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 1,
            'shipping_method' => ShippingMethod::PICKUP->value,
        ]);

        $secondResponse = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 1,
            'shipping_method' => ShippingMethod::PICKUP->value,
        ]);

        $this->assertSame(
            [201, 409],
            [
                $firstResponse->status(),
                $secondResponse->status(),
            ],
        );

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 0,
        ]);

        $this->assertDatabaseCount('orders', 1);

        $firstResponse->assertJsonStructure([
            'data' => [
                'id',
                'status',
                'shipping_method',
                'subtotal',
                'shipping_cost',
                'total',
                'created_at',
            ],
        ]);

        $secondResponse->assertJsonStructure([
            'message',
        ]);
    }

    public function test_orders_can_be_listed_with_status_filter_and_pagination(): void
    {
        Order::query()->create([
            'status' => OrderStatus::PAID,
            'shipping_method' => ShippingMethod::PICKUP,
            'subtotal' => 100_000,
            'shipping_cost' => 0,
            'total' => 100_000,
        ]);

        Order::query()->create([
            'status' => OrderStatus::SHIPPED,
            'shipping_method' => ShippingMethod::POST,
            'subtotal' => 100_000,
            'shipping_cost' => 30_000,
            'total' => 130_000,
        ]);

        Order::query()->create([
            'status' => OrderStatus::SHIPPED,
            'shipping_method' => ShippingMethod::TIPAX,
            'subtotal' => 200_000,
            'shipping_cost' => 40_000,
            'total' => 240_000,
        ]);

        $response = $this->getJson(
            '/api/orders?status=shipped&per_page=1',
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'status',
                        'shipping_method',
                        'subtotal',
                        'shipping_cost',
                        'total',
                        'created_at',
                    ],
                ],
                'links',
                'meta',
            ]);

        $response->assertJsonPath('meta.per_page', 1);
        $response->assertJsonPath('meta.total', 2);

        $this->assertDatabaseHas('orders', [
            'status' => OrderStatus::SHIPPED->value,
        ]);
    }

    public function test_shipped_orders_older_than_24_hours_are_delivered(): void
    {
        $expiredOrder = Order::query()->create([
            'status' => OrderStatus::SHIPPED,
            'shipping_method' => ShippingMethod::POST,
            'subtotal' => 100_000,
            'shipping_cost' => 30_000,
            'total' => 130_000,
            'shipped_at' => now()->subHours(25),
        ]);

        $recentOrder = Order::query()->create([
            'status' => OrderStatus::SHIPPED,
            'shipping_method' => ShippingMethod::POST,
            'subtotal' => 100_000,
            'shipping_cost' => 30_000,
            'total' => 130_000,
            'shipped_at' => now()->subHours(23),
        ]);

        $paidOrder = Order::query()->create([
            'status' => OrderStatus::PAID,
            'shipping_method' => ShippingMethod::POST,
            'subtotal' => 100_000,
            'shipping_cost' => 30_000,
            'total' => 130_000,
        ]);

        $this->artisan('orders:deliver-expired')
            ->assertExitCode(0);

        $this->assertDatabaseHas('orders', [
            'id' => $expiredOrder->id,
            'status' => OrderStatus::DELIVERED->value,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $recentOrder->id,
            'status' => OrderStatus::SHIPPED->value,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $paidOrder->id,
            'status' => OrderStatus::PAID->value,
        ]);
    }

    public function test_shipping_an_order_records_shipped_at(): void
    {
        $order = Order::query()->create([
            'status' => OrderStatus::PAID,
            'shipping_method' => ShippingMethod::PICKUP,
            'subtotal' => 100_000,
            'shipping_cost' => 0,
            'total' => 100_000,
        ]);

        $repository = app(
            OrderRepositoryInterface::class,
        );

        $repository->updateStatus(
            $order,
            OrderStatus::SHIPPED,
        );

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::SHIPPED->value,
        ]);

        $this->assertNotNull(
            $order->refresh()->shipped_at,
        );
    }

    public function test_paid_order_can_be_shipped(): void
    {
        $order = Order::query()->create([
            'status' => OrderStatus::PAID,
            'shipping_method' => ShippingMethod::POST,
            'subtotal' => 100_000,
            'shipping_cost' => 30_000,
            'total' => 130_000,
        ]);

        $response = $this->patchJson(
            "/api/orders/{$order->id}/ship",
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'shipping_method',
                    'subtotal',
                    'shipping_cost',
                    'total',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::SHIPPED->value,
        ]);

        $this->assertNotNull(
            $order->refresh()->shipped_at,
        );
    }

    public function test_delivered_order_cannot_be_shipped_again(): void
    {
        $order = Order::query()->create([
            'status' => OrderStatus::DELIVERED,
            'shipping_method' => ShippingMethod::POST,
            'subtotal' => 100_000,
            'shipping_cost' => 30_000,
            'total' => 130_000,
        ]);

        $response = $this->patchJson(
            "/api/orders/{$order->id}/ship",
        );

        $response
            ->assertStatus(409)
            ->assertJsonStructure([
                'message',
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::DELIVERED->value,
        ]);
    }
}
