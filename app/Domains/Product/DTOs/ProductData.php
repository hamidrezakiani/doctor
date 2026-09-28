<?php

declare(strict_types=1);

namespace App\Domains\Product\DTOs;

final readonly class ProductData
{
    public function __construct(
        public int $externalId,
        public string $title,
        public int $price,
        public int $stock,
    ) {}

    public static function fromApi(array $product): self
    {
        return new self(
            externalId: (int) $product['id'],
            title: $product['title'],
            price: (int) round((float) $product['price'] * 10000),
            stock: (int) $product['stock'],
        );
    }
}
