<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\TimestampsScope;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use SoftDeletes;
    use TimestampsScope;

    public const array STATUS_LIST = ['draft', 'active', 'archived'];

    public const string STATUS_DEFAULT = 'draft';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
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
     *
     * @return BelongsTo<ProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * Get the type that owns the product.
     *
     * @return BelongsTo<ProductType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    /**
     * Get the vendor that owns the product.
     *
     * @return BelongsTo<Vendor, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the media for the product.
     *
     * @return HasMany<ProductMedia, $this>
     */
    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class);
    }

    /**
     * Get the options for the product.
     *
     * @return HasMany<ProductOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class);
    }

    /**
     * Get the values for the product.
     *
     * @return HasManyThrough<ProductOptionValue, ProductOption, $this>
     */
    public function values(): HasManyThrough
    {
        return $this->hasManyThrough(ProductOptionValue::class, ProductOption::class, 'product_id', 'option_id');
    }

    /**
     * Get the variants for the product.
     *
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * The collections that belong to the product.
     *
     * @return BelongsToMany<Collection, $this, Pivot>
     */
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class);
    }

    /**
     * Get the product's thumbnail.
     */
    protected function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->media()->orderBy('rank')->value('url')
        );
    }

    /**
     * Get the product's stock.
     */
    protected function stock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->variants()->sum('quantity')
        );
    }

    /**
     * Get the product's tags.
     */
    protected function tags(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => filled($value) ? explode(',', (string) $value) : null,
            set: fn (mixed $value): ?string => filled($value) ? implode(',', $value) : null
        );
    }
}
