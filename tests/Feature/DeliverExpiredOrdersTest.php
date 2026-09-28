<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Order\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliverExpiredOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_shipped_orders_are_delivered(): void
    {
        $expiredOrder = Order::query()->create([
            'status' => OrderStatus::SHIPPED,
            'shipping_method' => 'post',
            'subtotal' => 100_000,
            'shipping_cost' => 30_000,
            'total' => 130_000,
            'shipped_at' => now()->subHours(25),
        ]);

        $recentOrder = Order::query()->create([
            'status' => OrderStatus::SHIPPED,
            'shipping_method' => 'post',
            'subtotal' => 100_000,
            'shipping_cost' => 30_000,
            'total' => 130_000,
            'shipped_at' => now()->subHours(23),
        ]);

        $paidOrder = Order::query()->create([
            'status' => OrderStatus::PAID,
            'shipping_method' => 'post',
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
}
