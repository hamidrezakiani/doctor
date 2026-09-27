<?php

declare(strict_types=1);

namespace App\Domains\Order\Strategies;

use App\Domains\Order\Enums\ShippingMethod;
use App\Domains\Order\Strategies\Contracts\ShippingCostStrategy;

final class PostShippingStrategy implements ShippingCostStrategy
{
    private const int COST = 30_000;

    public function supports(ShippingMethod $method): bool
    {
        return $method === ShippingMethod::POST;
    }

    public function calculate(): int
    {
        return self::COST;
    }
}
