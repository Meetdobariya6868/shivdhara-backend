<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DesignCodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A catalog product: a design from a company in a specific size/finish/
 * thickness, with default purchase & sale prices and packing info.
 */
class DesignCode extends Model
{
    /** @use HasFactory<DesignCodeFactory> */
    use HasFactory, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'company_id',
        'category_id',
        'size_id',
        'finish_id',
        'design_name',
        'design_code',
        'thickness',
        'purchase_price',
        'sale_price',
        'piece_per_box',
        'weight_per_box_kg',
        'photo',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'thickness' => 'integer',
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'piece_per_box' => 'decimal:2',
            'weight_per_box_kg' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<ProductSize, $this> */
    public function size(): BelongsTo
    {
        return $this->belongsTo(ProductSize::class, 'size_id');
    }

    /** @return BelongsTo<ProductFinish, $this> */
    public function finish(): BelongsTo
    {
        return $this->belongsTo(ProductFinish::class, 'finish_id');
    }

    /** @return HasMany<OrderItem, $this> */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
