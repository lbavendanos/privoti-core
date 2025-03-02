<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductVariantOption extends Pivot
{
    /**
     * Get the variant that owns the option.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Get the value that owns the option.
     */
    public function value(): BelongsTo
    {
        return $this->belongsTo(ProductOptionValue::class);
    }
}
