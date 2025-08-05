<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_LIST = ['draft', 'active', 'archived'];
    const STATUS_DEFAULT = 'draft';

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
     * Get the product's thumbnail.
     */
    protected function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->media()->orderBy('rank')->value('url')
        );
    }

    /**
     * Get the product's stock.
     */
    protected function stock(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->variants()->sum('quantity')
        );
    }

    /**
     * Get the product's tags.
     */
    protected function tags(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value) => filled($value) ? explode(',', $value) : null,
            set: fn(mixed $value) => filled($value) ? implode(',', $value) : null
        );
    }

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

    /**
     * Get the values for the product.
     */
    public function values(): HasManyThrough
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

    /**
     * The collections that belong to the product.
     */
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class);
    }

    /**
     * Scope a query to only include products created between the given dates.
     */
    #[Scope]
    protected function createdBetween(Builder $query, array $dates)
    {
        $timezone = config('app.timezone');
        [$start, $end] = array_map(fn($date) => Carbon::parse($date)->setTimezone($timezone), $dates);

        $query->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end);
    }

    /**
     * Scope a query to only include products created on a specific date.
     */
    #[Scope]
    protected function createdAt(Builder $query, $date)
    {
        $timezone = config('app.timezone');
        $date = Carbon::parse($date)->setTimezone($timezone);

        $query->whereDate('created_at', $date);
    }

    /**
     * Scope a query to only include products updated between the given dates.
     */
    #[Scope]
    protected function updatedBetween(Builder $query, array $dates)
    {
        $timezone = config('app.timezone');
        [$start, $end] = array_map(fn($date) => Carbon::parse($date)->setTimezone($timezone), $dates);

        $query->whereDate('updated_at', '>=', $start)
            ->whereDate('updated_at', '<=', $end);
    }

    /**
     * Scope a query to only include products updated on a specific date.
     */
    #[Scope]
    protected function updatedAt(Builder $query, $date)
    {
        $timezone = config('app.timezone');
        $date = Carbon::parse($date)->setTimezone($timezone);

        $query->whereDate('updated_at', $date);
    }
}
