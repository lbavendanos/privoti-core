<?php

declare(strict_types=1);

namespace App\Domains\Cms\Http\Controllers;

use App\Domains\Cms\Http\Resources\ProductCategoryCollection;
use App\Domains\Cms\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class ProductCategoryController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): ProductCategoryCollection
    {
        $request->validate([
            'all' => ['nullable', 'boolean'],
            'fields' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer'],
            'roots' => ['nullable', 'boolean'],
            'children' => ['nullable', 'boolean'],
            'q' => ['nullable', 'string'],
            'order' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer'],
        ]);

        $query = ProductCategory::query();

        if ($request->filled('fields')) {
            $query->select(explode(',', (string) $request->input('fields')));
        }

        $query->when($request->filled('parent_id'), fn ($q) => $q->where('parent_id', $request->input('parent_id')));
        $query->when($request->boolean('roots'), fn ($q) => $q->whereNull('parent_id'));
        $query->when($request->filled('q'), fn ($q) => $q->where('name', 'like', sprintf('%%%s%%', $request->input('q'))));

        if ($request->boolean('children')) {
            $query->with('children');
        }

        $orders = explode(',', (string) $request->input('order', 'id'));

        foreach ($orders as $order) {
            $direction = str_starts_with($order, '-') ? 'desc' : 'asc';
            $column = mb_ltrim($order, '-');

            $query->orderBy($column, $direction);
        }

        if ($request->boolean('all', false)) {
            return new ProductCategoryCollection($query->get());
        }

        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        return new ProductCategoryCollection($query->paginate($perPage, ['*'], 'page', $page));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): ProductCategoryResource
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

        $category = ProductCategory::query()->create($request->all());

        return new ProductCategoryResource($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $category): ProductCategoryResource
    {
        return new ProductCategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(): void
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
