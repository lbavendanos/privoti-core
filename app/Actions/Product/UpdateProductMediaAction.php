<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class UpdateProductMediaAction
{
    /**
     * Update product media.
     *
     * @param  list<array<string,mixed>>  $attributes
     * @return Collection<int,ProductMedia>
     */
    public function handle(Product $product, array $attributes): Collection
    {
        return DB::transaction(function () use ($product, $attributes): Collection {
            /** @var Collection<int,ProductMedia> $collection */
            $collection = collect();

            foreach ($attributes as $attribute) {
                if (! isset($attribute['id'])) {
                    throw new InvalidArgumentException('The id attribute is required for updating media.');
                }

                /** @var ProductMedia $media */
                $media = $product->media()->findOrFail($attribute['id']);
                /** @var array<string, mixed> $mediaAttributes */
                $mediaAttributes = Arr::only($attribute, ['rank']);
                $media->update($mediaAttributes);

                $collection->push($media);
            }

            return $collection;
        });
    }
}
