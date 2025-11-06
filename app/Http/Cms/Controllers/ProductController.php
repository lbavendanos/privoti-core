<?php

declare(strict_types=1);

namespace App\Http\Cms\Controllers;

use App\Actions\Product\CreateProductAction;
use App\Actions\Product\DeleteProductAction;
use App\Actions\Product\DeleteProductsAction;
use App\Actions\Product\GetProductAction;
use App\Actions\Product\GetProductsAction;
use App\Actions\Product\UpdateProductAction;
use App\Actions\Product\UpdateProductsAction;
use App\Http\Cms\Requests\Product\BulkDestroyProductRequest;
use App\Http\Cms\Requests\Product\BulkUpdateProductRequest;
use App\Http\Cms\Requests\Product\GetProductsRequest;
use App\Http\Cms\Requests\Product\StoreProductRequest;
use App\Http\Cms\Requests\Product\UpdateProductRequest;
use App\Http\Cms\Resources\ProductCollection;
use App\Http\Cms\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class ProductController
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetProductsRequest $request, GetProductsAction $action): ProductCollection
    {
        /** @var array<string,mixed> $filters */
        $filters = $request->validated();
        $resource = $action->handle($filters);

        return new ProductCollection($resource);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request, CreateProductAction $action): ProductResource
    {
        $attributes = $request->validated();
        $product = $action->handle($attributes);

        return new ProductResource($product);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product, GetProductAction $action): ProductResource
    {
        return new ProductResource($action->handle($product));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product, UpdateProductAction $action): ProductResource
    {
        $attributes = $request->validated();
        $product = $action->handle($product, $attributes);

        return new ProductResource($product);
    }

    /**
     * Bulk update multiple products, each with its own data.
     */
    public function bulkUpdate(BulkUpdateProductRequest $request, UpdateProductsAction $action): AnonymousResourceCollection
    {
        /** @var list<array<string, mixed>> $attributes */
        $attributes = $request->array('items');
        $updatedProducts = $action->handle($attributes);

        return ProductResource::collection($updatedProducts);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product, DeleteProductAction $action): Response
    {
        $action->handle($product);

        return response()->noContent();
    }

    /**
     * Remove multiple resources from storage.
     */
    public function bulkDestroy(BulkDestroyProductRequest $request, DeleteProductsAction $action): Response
    {
        /** @var list<int> $ids */
        $ids = $request->array('ids');
        $action->handle($ids);

        return response()->noContent();
    }
}
