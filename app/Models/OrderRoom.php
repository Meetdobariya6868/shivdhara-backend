<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderRoom extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'room_name',
        'sort_order',
        'total_sqft',
        'total_purchase',
        'total_sell',
        'total_profit',
    ];

    protected function casts(): array
    {
        return [
            'sort_order'    => 'integer',
            'total_sqft'    => 'decimal:4',
            'total_purchase'=> 'decimal:2',
            'total_sell'    => 'decimal:2',
            'total_profit'  => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'room_id');
    }
}
