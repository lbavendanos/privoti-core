<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\ProductMedia;
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

                ['id' => $id, 'rank' => $rank] = $attribute;
                /** @var ProductMedia $media */
                $media = $product->media()->findOrFail($id);
                $media->update(['rank' => $rank]);

                $collection->push($media);
            }

            return $collection;
        });
    }
}
