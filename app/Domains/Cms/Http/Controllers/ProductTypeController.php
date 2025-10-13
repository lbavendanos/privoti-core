<?php

declare(strict_types=1);

namespace App\Domains\Cms\Http\Controllers;

use App\Actions\ProductType\GetProductTypesAction;
use App\Domains\Cms\Http\Requests\ProductType\GetProductTypesRequest;
use App\Domains\Cms\Http\Resources\ProductTypeCollection;

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
