<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class CreateProductAction
{
    public function __construct(
        private GetProductAction $getProductAction
    ) {
        //
    }

    /**
     * Create a new product.
     *
     * @param  array<string,mixed>  $attributes
     */
    public function handle(array $attributes): Product
    {
        return DB::transaction(function () use ($attributes): Product {
            /** @var array<string,mixed> $attributes */
            $attributes = Arr::only($attributes, [
                'title',
                'subtitle',
                'handle',
                'description',
                'status',
                'tags',
                'metadata',
            ]);

            $attributes['handle'] = Str::slug(Arr::string($attributes, 'title'));

            if (! Arr::has($attributes, 'status')) {
                $attributes['status'] = Product::STATUS_DEFAULT;
            }

            $product = Product::query()->create($attributes);

            return $this->getProductAction->handle($product);
        });
    }
}
