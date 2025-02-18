<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'subtitle',
        'handle',
        'description',
        'status',
        'tags',
        'metadata',
        'category_id',
        'type_id',
        'vendor_id',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * Get the type that owns the product.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    /**
     * Get the vendor that owns the product.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the media for the product.
     */
    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class);
    }

    /**
     * Get the options for the product.
     */
    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class);
    }

    public function optionValues(): HasManyThrough
    {
        return $this->hasManyThrough(ProductOptionValue::class, ProductOption::class, 'product_id', 'option_id');
    }

    /**
     * Get the variants for the product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
