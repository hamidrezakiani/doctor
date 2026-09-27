<?php

declare(strict_types=1);

namespace App\Domains\Order\Enums;

enum OrderStatus: string
{
    case PAID = 'paid';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
}
