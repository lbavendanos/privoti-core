<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\TimestampsScope;
use Carbon\CarbonImmutable;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read int $id
 * @property-read string $title
 * @property-read string|null $subtitle
 * @property-read string $handle
 * @property-read string|null $description
 * @property-read string|null $thumbnail
 * @property-read int $stock
 * @property-read string $status
 * @property-read list<string>|null $tags
 * @property-read array<string,mixed>|null $metadata
 * @property-read int|null $category_id
 * @property-read int|null $type_id
 * @property-read int|null $vendor_id
 * @property-read CarbonImmutable|null $created_at
 * @property-read CarbonImmutable|null $updated_at
 * @property-read CarbonImmutable|null $deleted_at
 * @property-read ProductCategory|null $category
 * @property-read ProductType|null $type
 * @property-read Vendor|null $vendor
 * @property-read EloquentCollection<int, ProductMedia> $media
 * @property-read EloquentCollection<int, ProductOption> $options
 * @property-read EloquentCollection<int, ProductOptionValue> $values
 */
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
     *
     * @return Attribute<string|null, never>
     */
    protected function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->media()->orderBy('rank')->value('url')
        );
    }

    /**
     * Get the product's stock.
     *
     * @return Attribute<int, never>
     */
    protected function stock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->variants()->sum('quantity')
        );
    }

    /**
     * Get the product's tags.
     *
     * @return Attribute<list<string>|null, string|null>
     */
    protected function tags(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value): ?array {
                if (is_null($value)) {
                    return null;
                }

                if (! is_string($value)) {
                    return null;
                }

                return array_map('trim', explode(',', $value));
            },
            set: function (mixed $value): ?string {
                if (is_null($value)) {
                    return null;
                }

                if (! is_array($value)) {
                    return null;
                }

                return filled($value) ? implode(',', $value) : null;
            }
        );
    }
}
