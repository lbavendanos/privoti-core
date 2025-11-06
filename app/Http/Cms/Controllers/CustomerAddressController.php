<?php

declare(strict_types=1);

namespace App\Http\Cms\Controllers;

use App\Actions\CustomerAddress\CreateCustomerAddressAction;
use App\Actions\CustomerAddress\DeleteCustomerAddressAction;
use App\Actions\CustomerAddress\GetCustomerAddressAction;
use App\Actions\CustomerAddress\GetCustomerAddressesAction;
use App\Actions\CustomerAddress\UpdateCustomerAddressAction;
use App\Exceptions\MaxAddressesLimitExceededException;
use App\Http\Cms\Requests\CustomerAddress\GetCustomerAddressesRequest;
use App\Http\Cms\Requests\CustomerAddress\StoreCustomerAddressRequest;
use App\Http\Cms\Requests\CustomerAddress\UpdateCustomerAddressRequest;
use App\Http\Cms\Resources\CustomerAddressCollection;
use App\Http\Cms\Resources\CustomerAddressResource;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Http\Response;

final class CustomerAddressController
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetCustomerAddressesRequest $request, Customer $customer, GetCustomerAddressesAction $action): CustomerAddressCollection
    {
        /** @var array<string,mixed> $filters */
        $filters = $request->validated();
        $resource = $action->handle($customer, $filters);

        return new CustomerAddressCollection($resource);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerAddressRequest $request, Customer $customer, CreateCustomerAddressAction $action): CustomerAddressResource
    {
        $attributes = $request->validated();

        try {
            $address = $action->handle($customer, $attributes);
        } catch (MaxAddressesLimitExceededException $maxAddressesLimitExceededException) {
            abort(403, $maxAddressesLimitExceededException->getMessage());
        }

        return new CustomerAddressResource($address);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer, CustomerAddress $address, GetCustomerAddressAction $action): CustomerAddressResource
    {
        $address = $action->handle($customer, $address);

        return new CustomerAddressResource($address);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerAddressRequest $request, Customer $customer, CustomerAddress $address, UpdateCustomerAddressAction $action): CustomerAddressResource
    {
        $attributes = $request->validated();

        $address = $action->handle($customer, $address, $attributes);

        return new CustomerAddressResource($address);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer, CustomerAddress $address, DeleteCustomerAddressAction $action): Response
    {
        $action->handle($customer, $address);

        return response()->noContent();
    }
}
