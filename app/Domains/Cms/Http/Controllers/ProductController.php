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
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'active', 'archived'])],
            'tags' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', Rule::exists('product_categories', 'id')->where('is_active', true)],
            'type_id' => ['nullable', Rule::exists('product_types', 'id')],
            'vendor_id' => ['nullable', Rule::exists('vendors', 'id')],
            'media' => ['nullable', Rule::array()],
            'media.*.file' => ['required_with:media', File::image()->max('1mb')],
            // 'media.*.url' => ['required_with:media', 'url'],
            'media.*.rank' => ['required_with:media', 'integer'],
            'options' => ['nullable', Rule::array()],
            'options.*.name' => ['required_with:options', 'string', 'max:255'],
            'options.*.values' => ['required_with:options', Rule::array()],
        ]);

        // Create a handle from the title and unique
        $handle = Str::slug($request->input('title'));

        // Check if the handle already exists
        if (Product::where('handle', $handle)->exists()) {
            $handle = $handle . '-' . Str::random(5);
        }

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
                $path  = $file->storePubliclyAs('products', $handle . '-' . $key . '.' . $extension);
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
                $productOptionValues = array_map(fn($value) => ['value' => $value], $option['values']);

                $productOption->values()->createMany($productOptionValues);
            }
        }

        return new ProductResource($product->load('media', 'options.values'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->options->each(function ($option) {
            $option->values()->delete();
            $option->delete();
        });

        $product->media->each(function ($media) {
            Storage::delete($media->path);
            $media->delete();
        });

        $product->delete();

        return response()->noContent();
    }
}
