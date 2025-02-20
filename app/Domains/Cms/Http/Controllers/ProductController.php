<?php

namespace App\Domains\Cms\Http\Controllers;

use App\Domains\Cms\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
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

        $handle = Str::slug($request->input('title'));

        $request->merge(['handle' => $handle]);

        $product = Product::create($request->all());

        // Create product media
        if ($request->has('media')) {
            $mediaFiles = $request->file('media');
            $mediaInputs = $request->input('media');

            foreach ($mediaInputs as $key => $media) {
                $rank = $media['rank'];
                $file = $mediaFiles[$key]['file'];

                $extension = $file->extension();
                $filename = $handle . '-' . ($key + 1) . '-' . Str::uuid() . '.' . $extension;
                $path  = $file->storePubliclyAs('products', $filename);
                $url = Storage::url($path);

                $product->media()->create([
                    'url' => $url,
                    'rank' => $rank,
                ]);
            }
        }

        // Create product options
        if ($request->filled('options')) {
            $options = $request->input('options');

            foreach ($options as $option) {
                $productOption = $product->options()->create(['name' => $option['name']]);
                $valueArray = array_map(fn($value) => ['value' => $value], $option['values']);

                $productOption->values()->createMany($valueArray);
            }
        }

        // Create product variants
        if ($request->filled('variants')) {
            $variants = $request->input('variants');

            $product->load('values');

            foreach ($variants as $variant) {
                $productVariant = $product->variants()->create($variant);

                $optionValues = collect($variant['options'])
                    ->map(fn($option) => $product->values->firstWhere('value', $option['value']));

                $productVariant->values()->attach($optionValues->pluck('id'));
            }
        }

        return new ProductResource($product->load(
            'category',
            'type',
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

        // Update handle if title is changed
        if ($request->input('title') !== $product->title) {
            $handle = Str::slug($request->input('title'));

            $request->merge(['handle' => $handle]);
        }

        $product->update($request->all());

        // Update product media
        if ($request->has('media')) {
            $product->media()->delete();

            $mediaFiles = $request->file('media');
            $mediaInputs = $request->input('media');

            foreach ($mediaInputs as $key => $media) {
                $rank = $media['rank'];
                $file = $mediaFiles[$key]['file'];

                $extension = $file->extension();
                $path  = $file->storePubliclyAs('products', $product->handle . '-' . $key . '.' . $extension);
                $url = Storage::url($path);

                $product->media()->create([
                    'url' => $url,
                    'rank' => $rank,
                ]);
            }
        }

        // Update product options
        if ($request->filled('options')) {
            $product->options()->delete();

            $options = $request->input('options');

            foreach ($options as $option) {
                $productOption = $product->options()->create(['name' => $option['name']]);
                $valueArray = array_map(fn($value) => ['value' => $value], $option['values']);

                $productOption->values()->createMany($valueArray);
            }
        }

        // Update product variants
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

        return new ProductResource($product->load(
            'category',
            'type',
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
        // Delete product variants
        $product->variants()->delete();

        // Delete product media and options
        $product->values()->delete();
        $product->options()->delete();

        // Delete product media
        $product->media()->delete();

        // Delete product
        $product->delete();

        return response()->noContent();
    }
}
