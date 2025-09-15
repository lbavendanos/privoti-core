<?php

declare(strict_types=1);

namespace App\Domains\Cms\Http\Controllers;

use App\Actions\Customer\CreateGuestCustomerAction;
use App\Actions\Customer\DeleteCustomerAction;
use App\Actions\Customer\DeleteCustomersAction;
use App\Actions\Customer\GetCustomersAction;
use App\Actions\Customer\UpdateCustomerAction;
use App\Domains\Cms\Http\Requests\BulkDestroyCustomerRequest;
use App\Domains\Cms\Http\Requests\GetCustomersRequest;
use App\Domains\Cms\Http\Requests\StoreCustomerRequest;
use App\Domains\Cms\Http\Requests\UpdateCustomerRequest;
use App\Domains\Cms\Http\Resources\CustomerCollection;
use App\Domains\Cms\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Response;

final class CustomerController
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetCustomersRequest $request, GetCustomersAction $action): CustomerCollection
    {
        /** @var array<string,mixed> $filters */
        $filters = $request->validated();
        $resource = $action->handle($filters);

        return new CustomerCollection($resource);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request, CreateGuestCustomerAction $action): CustomerResource
    {
        $attributes = $request->validated();
        $customer = $action->handle($attributes);

        return new CustomerResource($customer->load('addresses'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer): CustomerResource
    {
        return new CustomerResource($customer->load('addresses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer, UpdateCustomerAction $action): CustomerResource
    {
        $attributes = $request->validated();
        $customer = $action->handle($customer, $attributes);

        return new CustomerResource($customer->load('addresses'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer, DeleteCustomerAction $action): Response
    {
        $action->handle($customer);

        return response()->noContent();
    }

    /**
     * Remove multiple resources from storage.
     */
    public function bulkDestroy(BulkDestroyCustomerRequest $request, DeleteCustomersAction $action): Response
    {
        /** @var list<int> $ids */
        $ids = $request->array('ids');
        $action->handle($ids);

        return response()->noContent();
    }
}
