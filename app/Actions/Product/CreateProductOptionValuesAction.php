<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final readonly class CreateProductOptionValuesAction
{
    /**
     * Create product option values.
     *
     * @param  list<string>  $attributes
     * @return Collection<int, ProductOptionValue>
     */
    public function handle(ProductOption $option, array $attributes): Collection
    {
        return DB::transaction(function () use ($option, $attributes): Collection {
            $attributes = array_values(array_unique($attributes));
            $values = array_map(fn (string $value): array => ['value' => $value], $attributes);

            return $option->values()->createMany($values);
        });
    }
}
