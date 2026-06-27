<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Enums\OrderStatus;
use Database\Factories\OrderStatusHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only audit record of an order status transition.
 */
class OrderStatusHistory extends Model
{
    /** @use HasFactory<OrderStatusHistoryFactory> */
    use HasFactory;

    protected $table = 'order_status_history';

    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $fillable = ['order_id', 'old_status', 'new_status', 'changed_by', 'remarks'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'old_status' => OrderStatus::class,
            'new_status' => OrderStatus::class,
        ];
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<User, $this> */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
