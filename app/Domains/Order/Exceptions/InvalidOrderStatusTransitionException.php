<?php

declare(strict_types=1);

namespace App\Domains\Order\Exceptions;

use RuntimeException;

final class InvalidOrderStatusTransitionException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Order cannot be shipped from its current status.');
    }
}
