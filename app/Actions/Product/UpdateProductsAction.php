<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class UpdateProductsAction
{
    public function __construct(private UpdateProductAction $action)
    {
        //
    }

    /**
     * Update multiple products by IDs.
     *
     * @param  list<array<string, mixed>>  $attributes
     * @return Collection<int,Product>
     */
    public function handle(array $attributes): Collection
    {
        return DB::transaction(function () use ($attributes): Collection {
            /** @var Collection<int,Product> $collection */
            $collection = collect();

            Product::query()
                ->whereIn('id', Arr::pluck($attributes, 'id'))
                ->chunkById(100, function ($products) use ($attributes, $collection): void {
                    foreach ($products as $product) {
                        /** @var array<string, mixed>|null $productAttributes */
                        $productAttributes = collect($attributes)
                            ->firstWhere('id', $product->id);

                        if ($productAttributes === null) {
                            continue;
                        }

                        $updatedProduct = $this->action->handle($product, $productAttributes);
                        $collection->push($updatedProduct);
                    }
                });

            return $collection;
        });

    }
}
