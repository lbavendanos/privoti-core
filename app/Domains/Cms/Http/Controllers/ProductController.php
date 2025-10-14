<?php

declare(strict_types=1);

namespace App\Domains\Cms\Http\Controllers;

use App\Actions\Product\GetProductsAction;
use App\Domains\Cms\Http\Requests\Product\GetProductsRequest;
use App\Domains\Cms\Http\Resources\ProductCollection;
use App\Domains\Cms\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use RuntimeException;

final class ProductController
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetProductsRequest $request, GetProductsAction $action): ProductCollection
    {
        /** @var array<string,mixed> $filters */
        $filters = $request->validated();
        $resource = $action->handle($filters);

        return new ProductCollection($resource);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): ProductResource
    {
        $rules = array_merge(
            $this->productRules(),
            $this->mediaRules(),
            $this->optionRules(),
            $this->variantRules()
        );

        $request->validate($rules);

        $handle = Str::slug($request->string('title')->value());

        $request->merge(['handle' => $handle]);

        if ($request->missing('status')) {
            $request->merge(['status' => Product::STATUS_DEFAULT]);
        }

        /** @var array<string,mixed> $attributes */
        $attributes = $request->all();
        $product = Product::query()->create($attributes);

        $this->createMedia($request, $product);
        $this->createOptions($request, $product);
        $this->createVariants($request, $product);
        $this->attachCollections($request, $product);

        return new ProductResource($product->load(
            'category',
            'type',
            'vendor',
            'media',
            'collections',
            'options.values',
            'variants.values'
        ));
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load(
            'category',
            'type',
            'vendor',
            'media',
            'collections',
            'options.values',
            'variants.values'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): ProductResource
    {
        $rules = array_merge(
            $this->productRules($product),
            $this->mediaRules($product),
            $this->optionRules($product),
            $this->variantRules($product)
        );

        $request->validate($rules);

        if ($request->has('title') && ($request->filled('title') && $request->input('title') !== $product->title)) {
            $handle = Str::slug($request->string('title')->value());
            $request->merge(['handle' => $handle]);
        }

        /** @var array<string,mixed> $attributes */
        $attributes = $request->all();
        $product->update($attributes);

        $this->updateOrCreateMedia($request, $product);
        $this->updateOrCreateOptions($request, $product);
        $this->updateOrCreateVariants($request, $product);
        $this->syncCollections($request, $product);

        return new ProductResource($product->load(
            'category',
            'type',
            'vendor',
            'media',
            'collections',
            'options.values',
            'variants.values'
        ));
    }

    /**
     * Bulk update multiple products, each with its own data.
     */
    public function bulkUpdate(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', Rule::exists('products', 'id')->withoutTrashed()],
            'items.*' => ['required', 'array'],
        ]);

        $updatedProducts = [];

        /** @var array<string,mixed> $item */
        foreach ($request->array('items') as $item) {
            /** @var Product $product */
            $product = Product::query()->findOrFail($item['id']);
            $data = Arr::except($item, ['id']);
            $updateRequest = new Request($data);

            $updatedProducts[] = $this->update($updateRequest, $product);
        }

        return ProductResource::collection($updatedProducts);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): Response
    {
        $this->deleteProduct($product);

        return response()->noContent();
    }

    /**
     * Remove multiple resources from storage.
     */
    public function bulkDestroy(Request $request): Response
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', Rule::exists('products', 'id')->withoutTrashed()],
        ]);

        Product::query()->whereIn('id', $request->input('ids'))
            ->chunkById(100, function ($products): void {
                foreach ($products as $product) {
                    $this->deleteProduct($product);
                }
            });

        return response()->noContent();
    }

    /**
     * Product rules.
     *
     * @return array<string,mixed>
     */
    private function productRules(?Product $product = null): array
    {
        return [
            'title' => $product instanceof Product ? ['sometimes', 'required', 'string', 'max:255', Rule::unique('products')->ignore($product->id)->withoutTrashed()] : ['required', 'string', 'max:255', Rule::unique('products')->withoutTrashed()],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(Product::STATUS_LIST)],
            'tags' => ['nullable', 'array'],
            'category_id' => ['nullable', Rule::exists('product_categories', 'id')->where('is_active', true)->withoutTrashed()],
            'type_id' => ['nullable', Rule::exists('product_types', 'id')->withoutTrashed()],
            'vendor_id' => ['nullable', Rule::exists('vendors', 'id')->withoutTrashed()],
            'collections' => ['nullable', 'array'],
        ];
    }

    /**
     * Media rules.
     *
     * @return array<string,mixed>
     */
    private function mediaRules(?Product $product = null): array
    {
        return [
            'media' => ['nullable', 'array'],
            'media.*.id' => $product instanceof Product ? ['nullable', Rule::exists('product_media', 'id')->where('product_id', $product->id)->withoutTrashed()] : [],
            'media.*.file' => ['required_without:media.*.id', File::image()->max('1mb')],
            'media.*.rank' => ['required_with:media', 'integer'],
        ];
    }

    /**
     * Option rules.
     *
     * @return array<string,mixed>
     */
    private function optionRules(?Product $product = null): array
    {
        return [
            'options' => ['nullable', 'array'],
            'options.*.id' => $product instanceof Product ? ['nullable', Rule::exists('product_options', 'id')->where('product_id', $product->id)->withoutTrashed()] : [],
            'options.*.name' => ['required_with:options', 'string', 'max:255'],
            'options.*.values' => ['nullable', 'array'],
            'options.*.values.*' => ['required_with:options.*.values', 'string', 'max:255'],
        ];
    }

    /**
     * Variant rules.
     *
     * @return array<string,mixed>
     */
    private function variantRules(?Product $product = null): array
    {
        return [
            'variants' => ['nullable', 'array'],
            'variants.*.id' => $product instanceof Product ? ['nullable', Rule::exists('product_variants', 'id')->where('product_id', $product->id)->withoutTrashed()] : [],
            'variants.*.name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.price' => ['required_with:variants', 'numeric'],
            'variants.*.quantity' => ['required_with:variants', 'integer'],
            'variants.*.options' => ['required_with:variants', 'array'],
            'variants.*.options.*.value' => ['required_with:variants', 'string', 'max:255'],
        ];
    }

    /**
     * Create product media.
     */
    private function createMedia(Request $request, Product $product): void
    {
        if ($request->filled('media')) {
            /** @var array<int,array{'file':UploadedFile, 'rank': int}> $inputMedia */
            $inputMedia = $request->array('media');

            foreach ($inputMedia as $key => $input) {
                $file = $request->file(sprintf('media.%s.file', $key));
                $url = $this->storeMediaFile($file, $product->handle.'-'.($key + 1));

                $product->media()->create([
                    'url' => $url,
                    'rank' => $input['rank'],
                ]);
            }
        }
    }

    /**
     * Update or create product media.
     */
    private function updateOrCreateMedia(Request $request, Product $product): void
    {
        if ($request->has('media')) {
            if ($request->isNotFilled('media')) {
                $product->media()->delete();

                return;
            }

            $existingMedia = $product->media()->pluck('id')->toArray();
            /** @var Collection<int, array{'id'?: int, 'file': UploadedFile, 'rank': int}> $inputMedia */
            $inputMedia = collect($request->array('media'));

            $mediaToKeep = $inputMedia->filter(fn ($media): bool => isset($media['id']))->pluck('id')->toArray();
            $mediaToDelete = array_diff($existingMedia, $mediaToKeep);

            if (filled($mediaToDelete)) {
                $product->media()->whereIn('id', $mediaToDelete)->delete();
            }

            foreach ($inputMedia as $key => $input) {
                if (isset($input['id'])) {
                    $existingMedia = $product->media()->find($input['id']);

                    if ($existingMedia) {
                        $existingMedia->update(['rank' => $input['rank']]);
                    }
                } else {
                    $file = $request->file(sprintf('media.%s.file', $key));
                    $url = $this->storeMediaFile($file, $product->handle.'-'.($key + 1));

                    $product->media()->create([
                        'url' => $url,
                        'rank' => $input['rank'],
                    ]);
                }
            }
        }
    }

    /**
     * Store media file.
     */
    private function storeMediaFile(UploadedFile $file, string $name): string
    {
        $extension = $file->extension();
        $filename = $name.'-'.Str::uuid().'.'.$extension;
        $path = $file->storePubliclyAs('products', $filename);

        if ($path === false) {
            throw new RuntimeException('Failed to store media file.');
        }

        return Storage::url($path);
    }

    /**
     * Create product options.
     */
    private function createOptions(Request $request, Product $product): void
    {
        if ($request->filled('options')) {
            /** @var array<int,array{'name': string, 'values'?: list<string>}> $inputOptions */
            $inputOptions = $request->array('options');

            foreach ($inputOptions as $input) {
                $option = $product->options()->create(['name' => $input['name']]);

                if (isset($input['values'])) {
                    $values = array_map(fn (string $value): array => ['value' => $value], $input['values']);

                    $option->values()->createMany($values);
                }
            }
        }
    }

    /**
     * Update or create product options.
     */
    private function updateOrCreateOptions(Request $request, Product $product): void
    {
        if ($request->has('options')) {
            if ($request->isNotFilled('options')) {
                $product->values()->delete();
                $product->options()->delete();

                return;
            }

            $existingOptions = $product->options()->pluck('id')->toArray();
            /** @var Collection<int, array{'id'?: int, 'name': string, 'values'?: list<string>}> $inputOptions */
            $inputOptions = collect($request->array('options'));

            $optionsToKeep = $inputOptions->filter(fn ($option): bool => isset($option['id']))->pluck('id')->toArray();
            $optionsToDelete = array_diff($existingOptions, $optionsToKeep);

            if (filled($optionsToDelete)) {
                $product->values()->whereIn('option_id', $optionsToDelete)->delete();
                $product->options()->whereIn('id', $optionsToDelete)->delete();
            }

            foreach ($inputOptions as $input) {
                if (isset($input['id'])) {
                    $existingOption = $product->options()->find($input['id']);

                    if ($existingOption) {
                        $existingOption->update(['name' => $input['name']]);

                        $this->updateOrCreateValues($input, $existingOption);
                    }
                } else {
                    $option = $product->options()->create(['name' => $input['name']]);

                    if (isset($input['values'])) {
                        $values = array_map(fn (string $value): array => ['value' => $value], $input['values']);

                        $option->values()->createMany($values);
                    }
                }
            }
        }
    }

    /**
     * Update or create option values.
     *
     * @param  array<string,mixed>  $inputOption
     */
    private function updateOrCreateValues(array $inputOption, ProductOption $option): void
    {
        if (Arr::has($inputOption, 'values')) {
            if (blank($inputOption['values'])) {
                $option->values()->delete();

                return;
            }

            $existingValues = $option->values()->pluck('value')->toArray();

            /** @var list<string> $valuesToKeep */
            $valuesToKeep = $inputOption['values'];
            $valuesToDelete = array_diff($existingValues, $valuesToKeep);

            if (filled($valuesToDelete)) {
                $option->values()->whereIn('value', $valuesToDelete)->delete();
            }

            $valuesToCreate = array_diff($valuesToKeep, $existingValues);
            $values = array_map(fn (string $value): array => ['value' => $value], $valuesToCreate);

            $option->values()->createMany($values);
        }
    }

    /**
     * Create product variants.
     */
    private function createVariants(Request $request, Product $product): void
    {
        if ($request->filled('variants')) {
            /** @var array<int,array{'name': string, 'price': float, 'quantity': int, 'options': list<array{'value': string}>}> $inputVariants */
            $inputVariants = $request->input('variants');

            foreach ($inputVariants as $input) {
                $variant = $product->variants()->create($input);
                $values = collect($input['options'])
                    ->map(fn ($option) => $product->values()->firstWhere('value', $option['value']));

                $variant->values()->attach($values->pluck('id'));
            }
        }
    }

    /**
     * Update or create product variants.
     */
    private function updateOrCreateVariants(Request $request, Product $product): void
    {
        if ($request->has('variants')) {
            if ($request->isNotFilled('variants')) {
                $product->variants()->delete();

                return;
            }

            $existingVariants = $product->variants()->pluck('id')->toArray();
            /** @var Collection<int, array{'id'?: int, 'name': string, 'price': float, 'quantity': int, 'options': list<array{'value': string}>}> $inputVariants */
            $inputVariants = collect($request->array('variants'));

            $variantsToKeep = $inputVariants->filter(fn ($variant): bool => isset($variant['id']))->pluck('id')->toArray();
            $variantsToDelete = array_diff($existingVariants, $variantsToKeep);

            if (filled($variantsToDelete)) {
                $product->variants()->whereIn('id', $variantsToDelete)->delete();
            }

            foreach ($inputVariants as $input) {
                if (isset($input['id'])) {
                    $existingVariant = $product->variants()->find($input['id']);

                    if ($existingVariant) {
                        $existingVariant->update($input);

                        $values = collect($input['options'])
                            ->map(fn ($option) => $product->values()->firstWhere('value', $option['value']));

                        $existingVariant->values()->sync($values->pluck('id'));
                    }
                } else {
                    $variant = $product->variants()->create($input);
                    $values = collect($input['options'])
                        ->map(fn ($option) => $product->values()->firstWhere('value', $option['value']));

                    $variant->values()->attach($values->pluck('id'));
                }
            }
        }
    }

    /**
     * Attach collections to product.
     */
    private function attachCollections(Request $request, Product $product): void
    {
        if ($request->filled('collections')) {
            $collections = $request->array('collections');

            $product->collections()->attach($collections);
        }
    }

    /**
     * Sync collections with product.
     */
    private function syncCollections(Request $request, Product $product): void
    {
        if ($request->has('collections')) {
            $collections = $request->array('collections');

            $product->collections()->sync($collections);
        }
    }

    /**
     * Delete product and its related data.
     */
    private function deleteProduct(Product $product): void
    {
        $product->variants()->delete();
        $product->values()->delete();
        $product->options()->delete();
        $product->media()->delete();
        $product->delete();
    }
}
