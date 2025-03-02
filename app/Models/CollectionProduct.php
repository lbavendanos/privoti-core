<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CollectionProduct extends Pivot
{
    /**
     * Get the collection that owns the product.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Get the product that owns the collection.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
