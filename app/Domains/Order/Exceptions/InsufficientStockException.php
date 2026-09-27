<?php

declare(strict_types=1);

namespace App\Domains\Order\Exceptions;

use RuntimeException;

final class InsufficientStockException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Insufficient product stock.');
    }
}
