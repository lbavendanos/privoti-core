<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

final readonly class DeleteProductAction
{
    /**
     * Delete a product.
     */
    public function handle(Product $product): void
    {
        DB::transaction(function () use ($product): void {
            $product->variants()->delete();
            $product->values()->delete();
            $product->options()->delete();
            $product->media()->delete();
            $product->delete();
        });
    }
}
