<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductMedia extends Model
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
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the name attribute.
     *
     * @return Attribute
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn() => pathinfo(parse_url($this->url, PHP_URL_PATH), PATHINFO_FILENAME)
        );
    }

    /**
     * Get the path attribute.
     *
     * @return Attribute
     */
    protected function path(): Attribute
    {
        return Attribute::make(
            get: function () {
                $path = ltrim(parse_url($this->url, PHP_URL_PATH), '/');

                if (env('FILESYSTEM_DISK') === 's3' && env('AWS_ENDPOINT')) {
                    $bucket = env('AWS_BUCKET');

                    if (str_starts_with($path, $bucket . '/')) {
                        $path = substr($path, strlen($bucket) + 1);
                    }
                }

                return $path;
            },
        );
    }

    /**
     * Get the type attribute.
     *
     * @return Attribute
     */
    protected function type(): Attribute
    {
        return Attribute::make(
            get: function () {
                $extension = pathinfo(parse_url($this->url, PHP_URL_PATH), PATHINFO_EXTENSION);

                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
                $videoExtensions = ['mp4', 'mkv', 'mov', 'avi', 'flv', 'wmv', 'webm'];

                if (in_array(strtolower($extension), $imageExtensions)) return 'image';
                if (in_array(strtolower($extension), $videoExtensions)) return 'video';

                return 'unknown';
            },
        );
    }
}
