<?php

namespace App\Domains\Cms\Http\Controllers;

use App\Domains\Cms\Http\Resources\ProductTypeCollection;
use App\Models\ProductType;
use Illuminate\Http\Request;

class ProductTypeController
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
            return new ProductTypeCollection(ProductType::all());
        }

        $query = ProductType::query();

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where('name', 'like', "%$search%");
        }

        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        $query->orderBy($sortBy, $sortOrder);

        return new ProductTypeCollection($query->paginate($perPage, ['*'], 'page', $page));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductType $type)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductType $type)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductType $type)
    {
        //
    }
}
