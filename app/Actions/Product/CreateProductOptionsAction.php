<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class CreateProductOptionsAction
{
    /**
     * Create product options.
     *
     * @param  list<array<string,mixed>>  $attributes
     * @return Collection<int, ProductOption>
     */
    public function handle(Product $product, array $attributes): Collection
    {
        return DB::transaction(function () use ($product, $attributes): Collection {
            /** @var Collection<int,ProductOption> $collection */
            $collection = collect();

            foreach ($attributes as $attribute) {
                /** @var array<string, mixed> $optionAttributes */
                $optionAttributes = Arr::only($attribute, ['name']);
                $option = $product->options()->create($optionAttributes);

                if (Arr::has($attribute, 'values')) {
                    /** @var list<string> $valueAttributes */
                    $valueAttributes = Arr::array($attribute, 'values');
                    $values = array_map(fn (string $value): array => ['value' => $value], $valueAttributes);

                    $option->values()->createMany($values);
                }

                $collection->push($option);
            }

            return $collection;
        });
    }
}
