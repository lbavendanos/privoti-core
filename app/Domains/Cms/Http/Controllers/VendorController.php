<?php

namespace App\Domains\Cms\Http\Controllers;

use App\Domains\Cms\Http\Resources\VendorCollection;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'all' => ['nullable', 'boolean'],
            'fields' => ['nullable', 'string'],
            'search' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer'],
            'order' => ['nullable', 'string'],
        ]);

        $query = Vendor::query();

        if ($request->filled('fields')) {
            $query->select(explode(',', $request->input('fields')));
        }

        $query->when($request->filled('search'), fn($q) => $q->where('name', 'like', "%{$request->input('search')}%"));

        $orders = explode(',', $request->input('order', 'id'));

        foreach ($orders as $order) {
            $direction = str_starts_with($order, '-') ? 'desc' : 'asc';
            $column = ltrim($order, '-');

            $query->orderBy($column, $direction);
        }

        if ($request->boolean('all', false)) {
            return new VendorCollection($query->get());
        }

        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        return new VendorCollection($query->paginate($perPage, ['*'], 'page', $page));
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
    public function show(Vendor $vendor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vendor $vendor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor)
    {
        //
    }
}
