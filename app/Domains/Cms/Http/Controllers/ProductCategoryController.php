<?php

namespace App\Domains\Cms\Http\Controllers;

use App\Domains\Cms\Http\Resources\ProductCategoryCollection;
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
    public function index(Request $request)
    {
        $request->validate([
            'all' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer'],
            'sort_by' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'string'],
        ]);

        if ($request->boolean('all', false)) {
            return new ProductCategoryCollection(ProductCategory::all());
        }

        $query = ProductCategory::query();

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where('name', 'like', "%$search%");
        }

        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        $query->orderBy($sortBy, $sortOrder);

        return new ProductCategoryCollection($query->paginate($perPage, ['*'], 'page', $page));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('product_categories')->withoutTrashed()],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'is_public' => ['required', 'boolean'],
            'rank' => ['required', 'integer'],
            'metadata' => ['nullable', 'array'],
            'parent_id' => ['nullable', Rule::exists('product_categories', 'id')->withoutTrashed()],
        ]);

        $handle = Str::slug($request->input('name'));

        $request->merge(['handle' => $handle]);

        $category = ProductCategory::create($request->all());

        return new ProductCategoryResource($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $category)
    {
        return new ProductCategoryResource($category);
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
