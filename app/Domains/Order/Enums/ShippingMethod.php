<?php

declare(strict_types=1);

namespace App\Domains\Order\Enums;

enum ShippingMethod: string
{
    case POST = 'post';
    case TIPAX = 'tipax';
    case PICKUP = 'pickup';
}
