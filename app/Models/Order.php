<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Enums\OrderStatus;
use App\Domain\Enums\OrderType;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * The order aggregate root. Header money columns (total_*) are derived
 * snapshots kept in sync by the service layer; treat them as read-only
 * outside that layer.
 *
 * @property OrderType $order_type
 * @property OrderStatus $order_status
 */
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'order_number',
        'user_id',
        'customer_id',
        'architect_id',
        'category_id',
        'order_type',
        'order_status',
        'transportation_charge',
        'advance_amount',
        'discount_amount',
        'total_purchase',
        'total_profit',
        'total_price',
        'notes',
        'order_date',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'order_type' => OrderType::class,
            'order_status' => OrderStatus::class,
            'transportation_charge' => 'decimal:2',
            'advance_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_purchase' => 'decimal:2',
            'total_profit' => 'decimal:2',
            'total_price' => 'decimal:2',
            'order_date' => 'date',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return BelongsTo<Architect, $this> */
    public function architect(): BelongsTo
    {
        return $this->belongsTo(Architect::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return HasMany<OrderStatusHistory, $this> */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
}
