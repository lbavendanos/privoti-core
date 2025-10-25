<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class UpdateProductVariantsAction
{
    /**
     * Update product variants.
     *
     * @param  list<array<string,mixed>>  $attributes
     * @return Collection<int, ProductVariant>
     */
    public function handle(Product $product, array $attributes): Collection
    {
        return DB::transaction(function () use ($product, $attributes): Collection {
            /** @var Collection<int,ProductVariant> $collection */
            $collection = collect();

            foreach ($attributes as $attribute) {
                if (! Arr::has($attribute, 'id')) {
                    throw new InvalidArgumentException('The id attribute is required for updating variants.');
                }

                /** @var ProductVariant $variant */
                $variant = $product->variants()->findOrFail($attribute['id']);
                /** @var array<string, mixed> $variantAttributes */
                $variantAttributes = Arr::only($attribute, [
                    'name',
                    'price',
                    'quantity',
                    'sku',
                    'barcode',
                ]);

                $variant->update($variantAttributes);

                if (Arr::has($attribute, 'options')) {
                    /** @var list<array{value: string}> $options */
                    $options = Arr::array($attribute, 'options');
                    $values = collect($options)
                        ->map(fn (array $option) => $product->values()->firstWhere('value', $option['value']));

                    $variant->values()->sync($values->pluck('id'));
                }

                $collection->push($variant);
            }

            return $collection;
        });
    }
}
