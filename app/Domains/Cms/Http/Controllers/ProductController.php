<?php

namespace App\Domains\Cms\Http\Controllers;

use App\Domains\Cms\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class ProductController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = array_merge(
            $this->productRules(),
            $this->mediaRules(),
            $this->optionRules(),
            $this->variantRules()
        );

        $request->validate($rules);

        $handle = Str::slug($request->input('title'));

        $request->merge(['handle' => $handle]);

        $product = Product::create($request->all());

        $this->createMedia($request, $product);
        $this->createOptions($request, $product);
        $this->createVariants($request, $product);
        $this->attachCollections($request, $product);

        return new ProductResource($product->load(
            'category',
            'type',
            'vendor',
            'media',
            'options.values',
            'variants.values'
        ));
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
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
    public function update(Request $request, Product $product)
    {
        $rules = array_merge(
            $this->productRules($product),
            $this->mediaRules($product),
            $this->optionRules($product),
            $this->variantRules($product)
        );

        $request->validate($rules);

        if ($request->has('title')) {
            if ($request->filled('title') && $request->input('title') !== $product->title) {
                $handle = Str::slug($request->input('title'));

                $request->merge(['handle' => $handle]);
            }
        }

        $product->update($request->all());

        $this->updateOrCreateMedia($request, $product);
        $this->updateOrCreateOptions($request, $product);
        $this->updateOrCreateVariants($request, $product);
        $this->syncCollections($request, $product);

        return new ProductResource($product->load(
            'category',
            'type',
            'vendor',
            'media',
            'options.values',
            'variants.values'
        ));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->variants()->delete();
        $product->values()->delete();
        $product->options()->delete();
        $product->media()->delete();
        $product->delete();

        return response()->noContent();
    }

    /**
     * Product rules.
     */
    private function productRules(?Product $product = null)
    {
        return [
            'title' => $product ? ['sometimes', 'required', 'string', 'max:255', Rule::unique('products')->ignore($product->id)->withoutTrashed()] : ['required', 'string', 'max:255', Rule::unique('products')->withoutTrashed()],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['draft', 'active', 'archived'])],
            'tags' => ['nullable', 'array'],
            'category_id' => ['nullable', Rule::exists('product_categories', 'id')->where('is_active', true)->withoutTrashed()],
            'type_id' => ['nullable', Rule::exists('product_types', 'id')->withoutTrashed()],
            'vendor_id' => ['nullable', Rule::exists('vendors', 'id')->withoutTrashed()],
            'collections' => ['nullable', 'array'],
        ];
    }

    /**
     * Media rules.
     */
    private function mediaRules(?Product $product = null)
    {
        return [
            'media' => ['nullable', 'array'],
            'media.*.id' => $product ? ['nullable', Rule::exists('product_media', 'id')->where('product_id', $product->id)->withoutTrashed()] : [],
            'media.*.file' => ['required_without:media.*.id', File::image()->max('1mb')],
            'media.*.rank' => ['required_with:media', 'integer'],
        ];
    }

    /**
     * Option rules.
     */
    private function optionRules(?Product $product = null)
    {
        return [
            'options' => ['nullable', 'array'],
            'options.*.id' => $product ? ['nullable', Rule::exists('product_options', 'id')->where('product_id', $product->id)->withoutTrashed()] : [],
            'options.*.name' => ['required_with:options', 'string', 'max:255'],
            'options.*.values' => ['nullable', 'array'],
            'options.*.values.*' => ['required_with:options.*.values', 'string', 'max:255'],
        ];
    }

    /**
     * Variant rules.
     */
    private function variantRules(?Product $product = null)
    {
        return [
            'variants' => ['nullable', 'array'],
            'variants.*.id' => $product ? ['nullable', Rule::exists('product_variants', 'id')->where('product_id', $product->id)->withoutTrashed()] : [],
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
    private function createMedia(Request $request, Product $product)
    {
        if ($request->filled('media')) {
            $inputMedia = $request->input('media');

            foreach ($inputMedia as $key => $input) {
                $file = $request->file("media.{$key}.file");
                $url = $this->storeMediaFile($file, $product->handle . '-' . ($key + 1));

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
    private function updateOrCreateMedia(Request $request, Product $product)
    {
        if ($request->has('media')) {
            if ($request->isNotFilled('media')) {
                $product->media()->delete();

                return;
            }

            $existingMedia = $product->media()->pluck('id')->toArray();
            $inputMedia = collect($request->input('media'));

            $mediaToKeep = $inputMedia->filter(fn($media) => isset($media['id']))->pluck('id')->toArray();
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

                    continue;
                }

                $file = $request->file("media.{$key}.file");
                $url = $this->storeMediaFile($file, $product->handle . '-' . ($key + 1));

                $product->media()->create([
                    'url' => $url,
                    'rank' => $input['rank'],
                ]);
            }
        }
    }

    /**
     * Store media file.
     */
    private function storeMediaFile(UploadedFile $file, string $name)
    {
        $extension = $file->extension();
        $filename = $name . '-' . Str::uuid() . '.' . $extension;
        $path = $file->storePubliclyAs('products', $filename);
        $url = Storage::url($path);

        return $url;
    }

    /**
     * Create product options.
     */
    private function createOptions(Request $request, Product $product)
    {
        if ($request->filled('options')) {
            $inputOptions = $request->input('options');

            foreach ($inputOptions as $input) {
                $option = $product->options()->create(['name' => $input['name']]);

                if (isset($input['values'])) {
                    $values = array_map(fn($value) => ['value' => $value], $input['values']);

                    $option->values()->createMany($values);
                }
            }
        }
    }

    /**
     * Update or create product options.
     */
    private function updateOrCreateOptions(Request $request, Product $product)
    {
        if ($request->has('options')) {
            if ($request->isNotFilled('options')) {
                $product->values()->delete();
                $product->options()->delete();

                return;
            }

            $existingOptions = $product->options()->pluck('id')->toArray();
            $inputOptions = collect($request->input('options'));

            $optionsToKeep = $inputOptions->filter(fn($option) => isset($option['id']))->pluck('id')->toArray();
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

                    continue;
                }

                $option = $product->options()->create(['name' => $input['name']]);

                if (isset($input['values'])) {
                    $values = array_map(fn($value) => ['value' => $value], $input['values']);

                    $option->values()->createMany($values);
                }
            }
        }
    }

    /**
     * Update or create option values.
     */
    private function updateOrCreateValues(array $inputOption, ProductOption $option)
    {
        if (Arr::has($inputOption, 'values')) {
            if (blank($inputOption['values'])) {
                $option->values()->delete();

                return;
            }

            $existingValues = $option->values()->pluck('value')->toArray();

            $valuesToKeep = $inputOption['values'];
            $valuesToDelete = array_diff($existingValues, $valuesToKeep);

            if (filled($valuesToDelete)) {
                $option->values()->whereIn('value', $valuesToDelete)->delete();
            }

            $valuesToCreate = array_diff($valuesToKeep, $existingValues);
            $values = array_map(fn($value) => ['value' => $value], $valuesToCreate);

            $option->values()->createMany($values);
        }
    }

    /**
     * Create product variants.
     */
    private function createVariants(Request $request, Product $product)
    {
        if ($request->filled('variants')) {
            $inputVariants = $request->input('variants');

            foreach ($inputVariants as $input) {
                $variant = $product->variants()->create($input);
                $values = collect($input['options'])
                    ->map(fn($option) => $product->values()->firstWhere('value', $option['value']));

                $variant->values()->attach($values->pluck('id'));
            }
        }
    }

    /**
     * Update or create product variants.
     */
    private function updateOrCreateVariants(Request $request, Product $product)
    {
        if ($request->has('variants')) {
            if ($request->isNotFilled('variants')) {
                $product->variants()->delete();

                return;
            }

            $existingVariants = $product->variants()->pluck('id')->toArray();
            $inputVariants = collect($request->input('variants'));

            $variantsToKeep = $inputVariants->filter(fn($variant) => isset($variant['id']))->pluck('id')->toArray();
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
                            ->map(fn($option) => $product->values()->firstWhere('value', $option['value']));

                        $existingVariant->values()->sync($values->pluck('id'));
                    }

                    continue;
                }

                $variant = $product->variants()->create($input);
                $values = collect($input['options'])
                    ->map(fn($option) => $product->values()->firstWhere('value', $option['value']));

                $variant->values()->attach($values->pluck('id'));
            }
        }
    }

    /**
     * Attach collections to product.
     */
    private function attachCollections(Request $request, Product $product)
    {
        if ($request->filled('collections')) {
            $collections = $request->input('collections');

            $product->collections()->attach($collections);
        }
    }

    /**
     * Sync collections with product.
     */
    private function syncCollections(Request $request, Product $product)
    {
        if ($request->has('collections')) {
            $collections = $request->input('collections', []);

            $product->collections()->sync($collections);
        }
    }
}
