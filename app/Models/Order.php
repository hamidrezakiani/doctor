<?php

declare(strict_types=1);

namespace App\Models;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Enums\ShippingMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'status',
        'shipping_method',
        'subtotal',
        'shipping_cost',
        'total',
        'shipped_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'shipping_method' => ShippingMethod::class,
            'subtotal' => 'integer',
            'shipping_cost' => 'integer',
            'total' => 'integer',
            'shipped_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
