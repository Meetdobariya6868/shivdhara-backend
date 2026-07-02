<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Enums\ItemType;
use App\Domain\Enums\MeasurementUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'room_id',
        'design_variant_id',
        'product_image_path',
        'item_type',
        'quantity',
        'pieces_per_box',
        'measurement_unit',
        'height',
        'width',
        'sqft_rate',
        'price_per_item',
        'product_total',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'item_type'       => ItemType::class,
            'measurement_unit'=> MeasurementUnit::class,
            'quantity'        => 'integer',
            'pieces_per_box'  => 'integer',
            'sort_order'      => 'integer',
            'height'          => 'decimal:3',
            'width'           => 'decimal:3',
            'sqft_rate'       => 'decimal:2',
            'price_per_item'  => 'decimal:2',
            'product_total'   => 'decimal:2',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(OrderRoom::class, 'room_id');
    }

    public function designVariant(): BelongsTo
    {
        return $this->belongsTo(DesignVariant::class, 'design_variant_id');
    }
}
