<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'order_date',
        'customer_id',
        'order_category_id',
        'order_type_id',
        'creator_id',
        'advance_payment',
        'transportation_charge',
        'notes',
        'total_purchase_amount',
        'total_sell_amount',
        'total_profit',
        'grand_total',
        'balance_due',
        'updated_by_id',
    ];

    protected function casts(): array
    {
        return [
            'order_date'           => 'date',
            'advance_payment'      => 'decimal:2',
            'transportation_charge'=> 'decimal:2',
            'total_purchase_amount'=> 'decimal:2',
            'total_sell_amount'    => 'decimal:2',
            'total_profit'         => 'decimal:2',
            'grand_total'          => 'decimal:2',
            'balance_due'          => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderCategory(): BelongsTo
    {
        return $this->belongsTo(OrderCategory::class, 'order_category_id');
    }

    public function orderType(): BelongsTo
    {
        return $this->belongsTo(OrderType::class, 'order_type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(OrderRoom::class);
    }

    /** All items across every room in this order. */
    public function items(): HasManyThrough
    {
        return $this->hasManyThrough(
            OrderItem::class,
            OrderRoom::class,
            'order_id',  // FK on order_rooms
            'room_id',   // FK on order_items
        );
    }
}
