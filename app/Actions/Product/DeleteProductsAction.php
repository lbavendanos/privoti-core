<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

final readonly class DeleteProductsAction
{
    public function __construct(private DeleteProductAction $action)
    {
        //
    }

    /**
     * Delete multiple products by IDs.
     *
     * @param  list<int>  $ids
     */
    public function handle(array $ids): void
    {
        if (blank($ids)) {
            return;
        }

        DB::transaction(function () use ($ids): void {
            Product::query()
                ->whereIn('id', $ids)
                ->chunkById(100, function ($products): void {
                    foreach ($products as $product) {
                        $this->action->handle($product);
                    }
                });
        });
    }
}
