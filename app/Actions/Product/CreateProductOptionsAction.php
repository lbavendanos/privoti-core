<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class CreateProductOptionsAction
{
    /**
     * Create product options.
     *
     * @param  list<array{name: string, values?: list<string>}>  $attributes
     * @return Collection<int, ProductOption>
     */
    public function handle(Product $product, array $attributes): Collection
    {
        return DB::transaction(function () use ($product, $attributes): Collection {
            /** @var Collection<int,ProductOption> $collection */
            $collection = collect();

            foreach ($attributes as $attribute) {
                $option = $product->options()->create(['name' => $attribute['name']]);

                if (isset($attribute['values'])) {
                    $values = array_map(fn (string $value): array => ['value' => $value], $attribute['values']);

                    $option->values()->createMany($values);
                }

                $collection->push($option);
            }

            return $collection;
        });
    }
}
