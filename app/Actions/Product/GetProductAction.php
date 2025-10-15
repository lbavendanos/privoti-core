<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;

final readonly class GetProductAction
{
    /**
     * Get the given product.
     */
    public function handle(Product|int $product): Product
    {
        $relations = [
            'category',
            'type',
            'vendor',
            'collections',
            'media',
            'options.values',
            'variants.values',
        ];

        if (is_int($product)) {
            $product = Product::query()
                ->with($relations)
                ->findOrFail($product);
        } else {
            $product->load($relations);
        }

        return $product;
    }
}
