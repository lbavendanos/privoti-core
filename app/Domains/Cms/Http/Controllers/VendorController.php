<?php

declare(strict_types=1);

namespace App\Domains\Cms\Http\Controllers;

use App\Actions\Vendor\GetVendorsAction;
use App\Domains\Cms\Http\Requests\Vendor\GetVendorsRequest;
use App\Domains\Cms\Http\Resources\VendorCollection;

final class VendorController
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetVendorsRequest $request, GetVendorsAction $action): VendorCollection
    {
        /** @var array<string,mixed> $filters */
        $filters = $request->validated();
        $resource = $action->handle($filters);

        return new VendorCollection($resource);
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
