<?php

declare(strict_types=1);

namespace App\Domains\Order\Strategies;

use App\Domains\Order\Enums\ShippingMethod;
use App\Domains\Order\Strategies\Contracts\ShippingCostStrategy;
use InvalidArgumentException;

final readonly class ShippingCostResolver
{
    /**
     * @param  iterable<ShippingCostStrategy>  $strategies
     */
    public function __construct(
        private iterable $strategies,
    ) {}

    public function calculate(ShippingMethod $method): int
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($method)) {
                return $strategy->calculate();
            }
        }

        throw new InvalidArgumentException(
            "No shipping strategy found for [{$method->value}].",
        );
    }
}
