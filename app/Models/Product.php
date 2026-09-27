<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'external_id',
        'title',
        'price',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'external_id' => 'integer',
            'price' => 'integer',
            'stock' => 'integer',
        ];
    }
}
