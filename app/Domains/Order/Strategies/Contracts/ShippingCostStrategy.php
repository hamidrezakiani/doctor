<?php

declare(strict_types=1);

namespace App\Domains\Order\Strategies\Contracts;

use App\Domains\Order\Enums\ShippingMethod;

interface ShippingCostStrategy
{
    public function supports(ShippingMethod $method): bool;

    public function calculate(): int;
}
