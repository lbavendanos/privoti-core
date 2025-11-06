<?php

declare(strict_types=1);

namespace App\Http\Cms\Controllers;

use App\Actions\ProductCategory\GetProductCategoriesAction;
use App\Http\Cms\Requests\ProductCategory\GetProductCategoriesRequest;
use App\Http\Cms\Resources\ProductCategoryCollection;

final class ProductCategoryController
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetProductCategoriesRequest $request, GetProductCategoriesAction $action): ProductCategoryCollection
    {
        /** @var array<string,mixed> $filters */
        $filters = $request->validated();
        $resource = $action->handle($filters);

        return new ProductCategoryCollection($resource);
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
