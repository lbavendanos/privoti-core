<?php

declare(strict_types=1);

namespace App\Domains\Cms\Http\Controllers;

use App\Actions\CustomerAddress\CreateCustomerAddressAction;
use App\Actions\CustomerAddress\DeleteCustomerAddressAction;
use App\Actions\CustomerAddress\GetCustomerAddressAction;
use App\Actions\CustomerAddress\GetCustomerAddressesAction;
use App\Actions\CustomerAddress\UpdateCustomerAddressAction;
use App\Domains\Cms\Http\Requests\CustomerAddress\GetCustomerAddressesRequest;
use App\Domains\Cms\Http\Requests\CustomerAddress\StoreCustomerAddressRequest;
use App\Domains\Cms\Http\Requests\CustomerAddress\UpdateCustomerAddressRequest;
use App\Domains\Cms\Http\Resources\CustomerAddressCollection;
use App\Domains\Cms\Http\Resources\CustomerAddressResource;
use App\Exceptions\CannotDeleteDefaultAddressException;
use App\Exceptions\MaxAddressesLimitExceededException;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        try {
            $address = $action->handle($customer, $address);
        } catch (ModelNotFoundException) {
            abort(404, 'Customer address not found.');
        }

        return new CustomerAddressResource($address);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerAddressRequest $request, Customer $customer, CustomerAddress $address, UpdateCustomerAddressAction $action): CustomerAddressResource
    {
        $attributes = $request->validated();

        try {
            $address = $action->handle($customer, $address, $attributes);
        } catch (ModelNotFoundException) {
            abort(404, 'Customer address not found.');
        }

        return new CustomerAddressResource($address);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer, CustomerAddress $address, DeleteCustomerAddressAction $action): Response
    {
        try {
            $action->handle($customer, $address);
        } catch (ModelNotFoundException) {
            abort(404, 'Customer address not found.');
        } catch (CannotDeleteDefaultAddressException $cannotDeleteDefaultAddressException) {
            abort(403, $cannotDeleteDefaultAddressException->getMessage());
        }

        return response()->noContent();
    }
}
