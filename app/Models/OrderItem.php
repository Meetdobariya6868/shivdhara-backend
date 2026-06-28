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
        'pieces_per_box',
        'number_of_boxes',
        'number_of_pieces',
        'measurement_unit',
        'height',
        'width',
        'sqft_rate',
        'total_pieces',
        'purchase_amount',
        'sell_amount',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'item_type'       => ItemType::class,
            'measurement_unit'=> MeasurementUnit::class,
            'pieces_per_box'  => 'integer',
            'number_of_boxes' => 'integer',
            'number_of_pieces'=> 'integer',
            'total_pieces'    => 'integer',
            'sort_order'      => 'integer',
            'height'          => 'decimal:3',
            'width'           => 'decimal:3',
            'sqft_rate'       => 'decimal:2',
            'purchase_amount' => 'decimal:2',
            'sell_amount'     => 'decimal:2',
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
