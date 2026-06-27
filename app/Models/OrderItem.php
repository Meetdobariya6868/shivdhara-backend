<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Enums\ItemType;
use App\Domain\Enums\MeasurementUnit;
use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single line on an order. References a catalog product (design_code) for its
 * specs, but captures its own measurement, quantity and price snapshots.
 * Quantity, area and price columns are derived (persisted) results; the
 * calculation logic lives in the domain/service layer.
 *
 * @property ItemType $items_type
 * @property MeasurementUnit $size_unit
 */
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'order_id',
        'design_code_id',
        'item_index',
        'area_name',
        'custom_name',
        'items_type',
        'quantity',
        'piece_per_box',
        'total_pieces',
        'height',
        'width',
        'size_unit',
        'area_sqft',
        'total_sqft',
        'sqft_rate',
        'cost_rate',
        'unit_price',
        'cost_total',
        'line_total',
        'profit_amount',
        'photo_override',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'items_type' => ItemType::class,
            'size_unit' => MeasurementUnit::class,
            'item_index' => 'integer',
            'quantity' => 'decimal:2',
            'piece_per_box' => 'decimal:2',
            'total_pieces' => 'decimal:2',
            'height' => 'decimal:2',
            'width' => 'decimal:2',
            'area_sqft' => 'decimal:4',
            'total_sqft' => 'decimal:4',
            'sqft_rate' => 'decimal:2',
            'cost_rate' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'cost_total' => 'decimal:2',
            'line_total' => 'decimal:2',
            'profit_amount' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<DesignCode, $this> */
    public function designCode(): BelongsTo
    {
        return $this->belongsTo(DesignCode::class);
    }
}
