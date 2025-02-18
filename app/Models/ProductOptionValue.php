<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductOptionValue extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'value',
        'option_id',
    ];

    /**
     * Get the option that owns the value.
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class);
    }

    /**
     * The variants that belong to the option value.
     */
    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_options', 'option_value_id', 'variant_id');
    }
}
