<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductOptionValueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ProductOptionValue extends Model
{
    /** @use HasFactory<ProductOptionValueFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'value',
        'option_id',
    ];

    /**
     * Get the option that owns the value.
     *
     * @return BelongsTo<ProductOption, $this>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class);
    }

    /**
     * The variants that belong to the option value.
     *
     * @return BelongsToMany<ProductVariant, $this, Pivot>
     */
    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_options', 'value_id', 'variant_id');
    }
}
