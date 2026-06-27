<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductFinishFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductFinish extends Model
{
    /** @use HasFactory<ProductFinishFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['name'];

    /** @return HasMany<DesignCode, $this> */
    public function designCodes(): HasMany
    {
        return $this->hasMany(DesignCode::class, 'finish_id');
    }
}
