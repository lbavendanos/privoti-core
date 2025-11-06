<?php

declare(strict_types=1);

namespace App\Http\Cms\Controllers;

use App\Actions\ProductType\GetProductTypesAction;
use App\Http\Cms\Requests\ProductType\GetProductTypesRequest;
use App\Http\Cms\Resources\ProductTypeCollection;

final class ProductTypeController
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetProductTypesRequest $request, GetProductTypesAction $action): ProductTypeCollection
    {
        /** @var array<string,mixed> $filters */
        $filters = $request->validated();
        $resource = $action->handle($filters);

        return new ProductTypeCollection($resource);
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
