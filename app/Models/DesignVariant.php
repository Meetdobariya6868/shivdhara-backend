<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DesignVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'design_id',
        'size',
        'finish',
        'thickness',
        'purchase_rate',
        'sell_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_rate' => 'decimal:2',
            'sell_rate'     => 'decimal:2',
            'is_active'     => 'boolean',
        ];
    }

    public function design(): BelongsTo
    {
        return $this->belongsTo(Design::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'design_variant_id');
    }
}
