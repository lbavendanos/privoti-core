<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class CreateProductVariantsAction
{
    /**
     * Create product variants.
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
                /** @var array<string, mixed> $variantAttributes */
                $variantAttributes = Arr::only($attribute, [
                    'name',
                    'price',
                    'quantity',
                    'sku',
                    'barcode',
                ]);

                $variant = $product->variants()->create($variantAttributes);

                if (! Arr::has($attribute, 'options')) {
                    throw new InvalidArgumentException('Variant options are required.');
                }

                /** @var list<array{value: string}> $options */
                $options = Arr::array($attribute, 'options');
                $values = collect($options)
                    ->map(fn (array $option) => $product->values()->firstWhere('value', $option['value']));

                $variant->values()->attach($values->pluck('id'));

                $collection->push($variant);
            }

            return $collection;
        });
    }
}
