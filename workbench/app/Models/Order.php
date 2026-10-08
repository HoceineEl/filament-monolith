<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;
use Workbench\App\Enums\OrderStatus;

class Order extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'items' => 'array',
            'is_paid' => 'boolean',
            'placed_at' => 'date',
        ];
    }
}
