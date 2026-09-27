<?php

declare(strict_types=1);

namespace App\Domains\Order\Strategies;

use App\Domains\Order\Enums\ShippingMethod;
use App\Domains\Order\Strategies\Contracts\ShippingCostStrategy;

final class PickupShippingStrategy implements ShippingCostStrategy
{
    public function supports(ShippingMethod $method): bool
    {
        return $method === ShippingMethod::PICKUP;
    }

    public function calculate(): int
    {
        return 0;
    }
}
