<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductSizeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSize extends Model
{
    /** @use HasFactory<ProductSizeFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['label', 'width_value', 'height_value', 'unit', 'area_sqft'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'width_value' => 'decimal:2',
            'height_value' => 'decimal:2',
            'area_sqft' => 'decimal:4',
        ];
    }

    /** @return HasMany<DesignCode, $this> */
    public function designCodes(): HasMany
    {
        return $this->hasMany(DesignCode::class, 'size_id');
    }
}
