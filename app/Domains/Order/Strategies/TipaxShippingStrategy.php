<?php

declare(strict_types=1);

namespace App\Domains\Order\Strategies;

use App\Domains\Order\Enums\ShippingMethod;
use App\Domains\Order\Strategies\Contracts\ShippingCostStrategy;

final class TipaxShippingStrategy implements ShippingCostStrategy
{
    private const int COST = 40_000;

    public function supports(ShippingMethod $method): bool
    {
        return $method === ShippingMethod::TIPAX;
    }

    public function calculate(): int
    {
        return self::COST;
    }
}
