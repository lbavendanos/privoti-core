<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class UpdateProductAction
{
    public function __construct(
        private SyncProductMediaAction $syncProductMediaAction,
        private SyncProductOptionsAction $syncProductOptionsAction,
        private GetProductAction $getProductAction
    ) {
        //
    }

    /**
     * Update an existing product.
     *
     * @param  array<string,mixed>  $attributes
     */
    public function handle(Product $product, array $attributes): Product
    {
        return DB::transaction(function () use ($product, $attributes): Product {
            $basicAttributes = $this->prepareBasicAttributes($product, $attributes);

            $product->update($basicAttributes);

            if (Arr::has($attributes, 'media')) {
                /** @var list<array<string,mixed>> $media */
                $media = Arr::array($attributes, 'media');
                $this->syncProductMediaAction->handle($product, $media);
            }

            if (Arr::has($attributes, 'options')) {
                /** @var list<array<string,mixed>> $options */
                $options = Arr::array($attributes, 'options');
                $this->syncProductOptionsAction->handle($product, $options);
            }

            return $this->getProductAction->handle($product);
        });
    }

    /**
     * Prepare and sanitize product attributes.
     *
     * @param  array<string,mixed>  $attributes
     * @return array<string,mixed>
     */
    private function prepareBasicAttributes(Product $product, array $attributes): array
    {
        /** @var array<string,mixed> $basicAttributes */
        $basicAttributes = Arr::only($attributes, [
            'title',
            'subtitle',
            'handle',
            'description',
            'status',
            'tags',
            'metadata',
            'category_id',
            'type_id',
            'vendor_id',
        ]);

        if (Arr::has($basicAttributes, 'title') && (filled($basicAttributes['title']) && $basicAttributes['title'] !== $product->title)) {
            $basicAttributes['handle'] = Str::slug(Arr::string($basicAttributes, 'title'));
        }

        return $basicAttributes;
    }
}
