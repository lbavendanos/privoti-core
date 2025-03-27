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
            'fields' => ['nullable', 'string'],
            'q' => ['nullable', 'string'],
            'order' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer'],
        ]);

        $query = Collection::query();

        if ($request->filled('fields')) {
            $query->select(explode(',', $request->input('fields')));
        }

        $query->when($request->filled('q'), fn($q) => $q->where('title', 'like', "%{$request->input('q')}%"));

        $orders = explode(',', $request->input('order', 'id'));

        foreach ($orders as $order) {
            $direction = str_starts_with($order, '-') ? 'desc' : 'asc';
            $column = ltrim($order, '-');

            $query->orderBy($column, $direction);
        }

        if ($request->boolean('all', false)) {
            return new CollectionCollection($query->get());
        }

        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

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
