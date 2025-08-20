<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

final class ProductVariantOption extends Pivot
{
    use HasFactory;

    /**
     * Get the variant that owns the option.
     *
     * @return BelongsTo<ProductVariant, $this>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Get the value that owns the option.
     *
     * @return BelongsTo<ProductOptionValue, $this>
     */
    public function value(): BelongsTo
    {
        return $this->belongsTo(ProductOptionValue::class);
    }
}
