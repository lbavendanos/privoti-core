<?php

namespace App\Domains\Cms\Http\Controllers;

use App\Domains\Cms\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
        $this->validateCreateProduct($request);

        $handle = Str::slug($request->input('title'));

        $request->merge(['handle' => $handle]);

        $product = Product::create($request->all());

        $this->createMedia($request, $product);
        $this->createOrUpdateOptions($product, $request);
        $this->createOrUpdateVariants($product, $request);

        return new ProductResource($product->load('category', 'type', 'media', 'options.values', 'variants.values'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return new ProductResource($product->load(
            'category',
            'type',
            'media',
            'options.values',
            'variants.values'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $this->validateUpdateProduct($request, $product);

        if ($request->input('title') !== $product->title) {
            $handle = Str::slug($request->input('title'));
            $request->merge(['handle' => $handle]);
        }

        $product->update($request->all());

        $this->updateMedia($request, $product);
        // $this->createOrUpdateOptions($product, $request);
        // $this->createOrUpdateVariants($product, $request);

        return new ProductResource($product->load('category', 'type', 'media', 'options.values', 'variants.values'));
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

    private function validateCreateProduct(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('products')->withoutTrashed()],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'active', 'archived'])],
            'tags' => ['nullable', 'string', 'max:255'],

            'category_id' => ['nullable', Rule::exists('product_categories', 'id')->where('is_active', true)->withoutTrashed()],
            'type_id' => ['nullable', Rule::exists('product_types', 'id')->withoutTrashed()],
            'vendor_id' => ['nullable', Rule::exists('vendors', 'id')->withoutTrashed()],

            'media' => ['nullable', Rule::array()],
            'media.*.file' => ['required_with:media', File::image()->max('1mb')],
            'media.*.rank' => ['required_with:media', 'integer'],

            'options' => ['nullable', Rule::array()],
            'options.*.name' => ['required_with:options', 'string', 'max:255'],
            'options.*.values' => ['required_with:options', Rule::array()],
            'options.*.values.*' => ['required_with:options', 'string', 'max:255'],

            'variants' => ['nullable', Rule::array()],
            'variants.*.name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.price' => ['required_with:variants', 'numeric'],
            'variants.*.quantity' => ['required_with:variants', 'integer'],
            'variants.*.options' => ['required_with:variants', Rule::array()],
            'variants.*.options.*.value' => ['required_with:variants', 'string', 'max:255'],
        ]);
    }

    private function validateUpdateProduct(Request $request, Product $product)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($product->id)->withoutTrashed()],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'active', 'archived'])],
            'tags' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', Rule::exists('product_categories', 'id')->where('is_active', true)->withoutTrashed()],
            'type_id' => ['nullable', Rule::exists('product_types', 'id')->withoutTrashed()],
            'vendor_id' => ['nullable', Rule::exists('vendors', 'id')->withoutTrashed()],

            'media' => ['nullable', Rule::array()],
            'media.*.id' => ['nullable', Rule::exists('product_media', 'id')->where('product_id', $product->id)->withoutTrashed()],
            'media.*.file' => ['required_without:media.*.id', File::image()->max('1mb')],
            'media.*.rank' => ['required_with:media', 'integer'],

            'options' => ['nullable', Rule::array()],
            'options.*.name' => ['required_with:options', 'string', 'max:255'],
            'options.*.values' => ['required_with:options', Rule::array()],
            'options.*.values.*' => ['required_with:options', 'string', 'max:255'],

            'variants' => ['nullable', Rule::array()],
            'variants.*.name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.price' => ['required_with:variants', 'numeric'],
            'variants.*.quantity' => ['required_with:variants', 'integer'],
            'variants.*.options' => ['required_with:variants', Rule::array()],
            'variants.*.options.*.value' => ['required_with:variants', 'string', 'max:255'],
        ]);
    }

    /**
     * Create product media.
     */
    private function createMedia(Request $request, Product $product)
    {
        if ($request->has('media')) {
            $inputMedia = $request->input('media');

            foreach ($inputMedia as $key => $media) {
                $file = $request->file("media.{$key}.file");
                $url = $this->storeMediaFile($file, $product->handle . '-' . ($key + 1));

                $product->media()->create([
                    'url' => $url,
                    'rank' => $media['rank'],
                ]);
            }
        }
    }

    /**
     * Update product media.
     */
    private function updateMedia(Request $request, Product $product)
    {
        dd($request->has('media'));
        if (! $request->has('media')) {
            $product->media()->delete();
            return;
        }

        $existingMedia = $product->media()->pluck('id')->toArray();
        $inputMedia = collect($request->input('media'));

        $mediaToKeep = $inputMedia->filter(fn($media) => isset($media['id']))->pluck('id')->toArray();
        $mediaToDelete = array_diff($existingMedia, $mediaToKeep);

        $product->media()->whereIn('id', $mediaToDelete)->delete();

        foreach ($inputMedia as $key => $media) {
            if (isset($media['id'])) {
                $existingMedia = $product->media()->find($media['id']);
                $existingMedia->update(['rank' => $media['rank']]);

                continue;
            }

            $file = $request->file("media.{$key}.file");
            $url = $this->storeMediaFile($file, $product->handle . '-' . ($key + 1));

            $product->media()->create([
                'url' => $url,
                'rank' => $media['rank'],
            ]);
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
     * Create or update product options.
     */
    private function createOrUpdateOptions(Product $product, Request $request)
    {
        if ($request->filled('options')) {
            $product->options()->delete();

            $options = $request->input('options');

            foreach ($options as $option) {
                $productOption = $product->options()->create(['name' => $option['name']]);
                $valueArray = array_map(fn($value) => ['value' => $value], $option['values']);

                $productOption->values()->createMany($valueArray);
            }
        }
    }

    /**
     * Create or update product variants.
     */
    private function createOrUpdateVariants(Product $product, Request $request)
    {
        if ($request->filled('variants')) {
            $product->variants()->delete();

            $variants = $request->input('variants');

            $product->load('values');

            foreach ($variants as $variant) {
                $productVariant = $product->variants()->create($variant);

                $optionValues = collect($variant['options'])
                    ->map(fn($option) => $product->values->firstWhere('value', $option['value']));

                $productVariant->values()->attach($optionValues->pluck('id'));
            }
        }
    }
}
