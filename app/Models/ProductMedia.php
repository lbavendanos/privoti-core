<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\ProductMediaFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read int $id
 * @property-read string $url
 * @property-read string $name
 * @property-read string $type
 * @property-read int $rank
 * @property-read int $product_id
 * @property-read CarbonImmutable|null $created_at
 * @property-read CarbonImmutable|null $updated_at
 * @property-read CarbonImmutable|null $deleted_at
 * @property-read Product $product
 */
final class ProductMedia extends Model
{
    /** @use HasFactory<ProductMediaFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'url',
        'rank',
        'product_id',
    ];

    /**
     * Get the product that owns the media.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the name attribute.
     *
     * @return Attribute<string, never>
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                /** @var string $path */
                $path = parse_url($this->url, PHP_URL_PATH);

                return pathinfo($path, PATHINFO_FILENAME);

            }
        );
    }

    /**
     * Get the type attribute.
     *
     * @return Attribute<string, never>
     */
    protected function type(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                /** @var string $path */
                $path = parse_url($this->url, PHP_URL_PATH);
                $extension = pathinfo($path, PATHINFO_EXTENSION);

                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
                $videoExtensions = ['mp4', 'mkv', 'mov', 'avi', 'flv', 'wmv', 'webm'];

                if (in_array(mb_strtolower($extension), $imageExtensions)) {
                    return 'image';
                }

                if (in_array(mb_strtolower($extension), $videoExtensions)) {
                    return 'video';
                }

                return 'unknown';
            },
        );
    }
}
