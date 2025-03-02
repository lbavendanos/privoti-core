<?php

namespace App\Domains\Cms\Http\Controllers;

use App\Domains\Cms\Http\Resources\CollectionCollection;
use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController
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
            return new CollectionCollection(Collection::all());
        }

        $query = Collection::query();

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where('name', 'like', "%$search%");
        }

        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        $query->orderBy($sortBy, $sortOrder);

        return new CollectionCollection($query->paginate($perPage, ['*'], 'page', $page));
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
    public function show(Collection $collection)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Collection $collection)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Collection $collection)
    {
        //
    }
}
