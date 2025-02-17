<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantOption extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'variant_id',
        'option_value_id',
    ];

    /**
     * Get the variant that owns the option.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Get the option value that owns the option.
     */
    public function optionValue(): BelongsTo
    {
        return $this->belongsTo(ProductOptionValue::class);
    }
}
