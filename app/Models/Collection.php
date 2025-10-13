<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CollectionFactory;
use Illuminate\Database\Eloquent\Collection as ECollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read int $id
 * @property-read string $title
 * @property-read string $handle
 * @property-read string|null $description
 * @property-read array<string, mixed>|null $metadata
 * @property-read string|null $created_at
 * @property-read string|null $updated_at
 * @property-read string|null $deleted_at
 * @property-read ECollection<int, Product> $products
 */
final class Collection extends Model
{
    /** @use HasFactory<CollectionFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'handle',
        'description',
        'metadata',
    ];

    /**
     * The products that belong to the collection.
     *
     * @return BelongsToMany<Product, $this, Pivot>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
