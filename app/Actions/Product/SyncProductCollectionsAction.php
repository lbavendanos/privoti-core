<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

final readonly class SyncProductCollectionsAction
{
    /**
     * Sync collections for a product.
     *
     * @param  list<int>  $collectionIds
     */
    public function handle(Product $product, array $collectionIds): void
    {
        DB::transaction(fn () => $product->collections()->sync($collectionIds));
    }
}
