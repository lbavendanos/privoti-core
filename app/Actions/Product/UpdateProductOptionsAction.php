<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class UpdateProductOptionsAction
{
    public function __construct(
        private SyncProductOptionValuesAction $syncProductOptionValuesAction,
    ) {
        //
    }

    /**
     * Update product options.
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
                if (! Arr::has($attribute, 'id')) {
                    throw new InvalidArgumentException('The id attribute is required for updating options.');
                }

                /** @var ProductOption $option */
                $option = $product->options()->findOrFail($attribute['id']);
                /** @var array<string, mixed> $optionAttributes */
                $optionAttributes = Arr::only($attribute, ['name']);
                $option->update($optionAttributes);

                $collection->push($option);

                if (Arr::has($attribute, 'values')) {
                    /** @var list<string> $valueAttributes */
                    $valueAttributes = Arr::array($attribute, 'values');
                    $this->syncProductOptionValuesAction->handle($option, $valueAttributes);
                }
            }

            return $collection;
        });
    }
}
