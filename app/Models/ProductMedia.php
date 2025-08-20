<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ProductMedia extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
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
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (): string => pathinfo(parse_url($this->url, PHP_URL_PATH), PATHINFO_FILENAME)
        );
    }

    /**
     * Get the type attribute.
     */
    protected function type(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $extension = pathinfo(parse_url($this->url, PHP_URL_PATH), PATHINFO_EXTENSION);

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
