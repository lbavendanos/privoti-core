<?php

declare(strict_types=1);

namespace App\Domains\Cms\Http\Controllers;

use App\Actions\Collection\GetCollectionsAction;
use App\Domains\Cms\Http\Requests\Collection\GetCollectionsRequest;
use App\Domains\Cms\Http\Resources\CollectionCollection;

final class CollectionController
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetCollectionsRequest $request, GetCollectionsAction $action): CollectionCollection
    {
        /** @var array<string,mixed> $filters */
        $filters = $request->validated();
        $resource = $action->handle($filters);

        return new CollectionCollection($resource);
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
