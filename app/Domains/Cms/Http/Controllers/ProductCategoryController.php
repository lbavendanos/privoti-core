<?php

namespace App\Domains\Cms\Http\Controllers;

use App\Domains\Cms\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductCategoryController
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'is_public' => ['required', 'boolean'],
            'rank' => ['required', 'integer'],
            'metadata' => ['nullable', 'array'],
            'parent_id' => ['nullable', Rule::exists('product_categories', 'id')],
        ]);

        // Create a handle from the name and unique
        $handle = Str::slug($request->input('name'));

        // Check if the handle already exists
        if (ProductCategory::where('handle', $handle)->exists()) {
            $handle = $handle . '-' . Str::random(5);
        }

        $request->merge(['handle' => $handle]);

        $category = ProductCategory::create($request->all());

        return new ProductCategoryResource($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductCategory $category)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $category)
    {
        $category->delete();

        return response()->noContent();
    }
}
