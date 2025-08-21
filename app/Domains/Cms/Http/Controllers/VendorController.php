<?php

declare(strict_types=1);

namespace App\Domains\Cms\Http\Controllers;

use App\Domains\Cms\Http\Resources\VendorCollection;
use App\Models\Vendor;
use Illuminate\Http\Request;

final class VendorController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): VendorCollection
    {
        $request->validate([
            'all' => ['nullable', 'boolean'],
            'fields' => ['nullable', 'string'],
            'q' => ['nullable', 'string'],
            'order' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer'],
        ]);

        $query = Vendor::query();

        if ($request->filled('fields')) {
            $query->select(explode(',', $request->string('fields')->value()));
        }

        $query->when($request->filled('q'), fn ($q) => $q->where('name', 'like', sprintf('%%%s%%', $request->string('q')->value())));

        $orders = explode(',', $request->string('order', 'id')->value());

        foreach ($orders as $order) {
            $direction = str_starts_with($order, '-') ? 'desc' : 'asc';
            $column = mb_ltrim($order, '-');

            $query->orderBy($column, $direction);
        }

        if ($request->boolean('all', false)) {
            return new VendorCollection($query->get());
        }

        $perPage = $request->integer('per_page', 15);
        $page = $request->integer('page', 1);

        return new VendorCollection($query->paginate($perPage, ['*'], 'page', $page));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): void
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(): void
    {
        //
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
    public function destroy(): void
    {
        //
    }
}
