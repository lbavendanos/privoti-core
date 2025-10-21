<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class CreateProductAction
{
    public function __construct(
        private CreateProductMediaAction $createProductMediaAction,
        private CreateProductOptionsAction $createProductOptionsAction,
        private GetProductAction $getProductAction
    ) {
        //
    }

    /**
     * Create a new product.
     *
     * @param  array<string,mixed>  $attributes
     */
    public function handle(array $attributes): Product
    {
        return DB::transaction(function () use ($attributes): Product {
            $basicAttributes = $this->prepareBasicAttributes($attributes);

            $product = Product::query()->create($basicAttributes);

            if (Arr::has($attributes, 'media')) {
                /** @var list<array{'file':UploadedFile, 'rank': int}>  $media */
                $media = Arr::array($attributes, 'media');
                $this->createProductMediaAction->handle($product, $media);
            }

            if (Arr::has($attributes, 'options')) {
                /** @var array<int,array{'name': string, 'values'?: list<string>}> $options */
                $options = Arr::array($attributes, 'options');
                $this->createProductOptionsAction->handle($product, $options);
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
    private function prepareBasicAttributes(array $attributes): array
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
        ]);

        $basicAttributes['handle'] = Str::slug(Arr::string($basicAttributes, 'title'));
        $basicAttributes['status'] ??= Product::STATUS_DEFAULT;

        return $basicAttributes;
    }
}
