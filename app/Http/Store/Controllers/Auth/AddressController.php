<?php

declare(strict_types=1);

namespace App\Http\Store\Controllers\Auth;

use App\Http\Store\Controllers\Controller;
use App\Http\Store\Resources\AddressResource;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class AddressController extends Controller
{
    private const int ADDRESS_LIMIT = 5;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var Customer $customer */
        $customer = $request->user();

        return AddressResource::collection($customer->addresses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): AddressResource
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'address1' => ['required', 'string', 'max:255'],
            'address2' => ['nullable', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
        ]);

        /** @var Customer $customer */
        $customer = $request->user();
        $numberOfAddresses = $customer->addresses()->count();

        if ($numberOfAddresses === self::ADDRESS_LIMIT) {
            abort(403, 'You can not add more than '.self::ADDRESS_LIMIT.' addresses.');
        }

        $request->merge(['default' => $numberOfAddresses === 0]);

        /** @var array<string,mixed> $attributes */
        $attributes = $request->all();
        $address = $customer->addresses()->create($attributes);

        return new AddressResource($address);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, CustomerAddress $address): AddressResource
    {
        /** @var Customer $customer */
        $customer = $request->user();

        if ($address->customer_id !== $customer->id) {
            abort(403);
        }

        return new AddressResource($address);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomerAddress $address): AddressResource
    {
        /** @var Customer $customer */
        $customer = $request->user();

        if ($address->customer_id !== $customer->id) {
            abort(403);
        }

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'address1' => ['required', 'string', 'max:255'],
            'address2' => ['nullable', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
        ]);

        /** @var array<string,mixed> $attributes */
        $attributes = $request->all();
        $address->update($attributes);

        return new AddressResource($address);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, CustomerAddress $address): Response
    {
        /** @var Customer $customer */
        $customer = $request->user();

        if ($address->customer_id !== $customer->id) {
            abort(403);
        }

        if ($address->default) {
            abort(403, 'You can not delete your default address.');
        }

        $address->delete();

        return response()->noContent();
    }

    /**
     * Set the specified address as default.
     */
    public function setDefault(Request $request, CustomerAddress $address): AddressResource
    {
        /** @var Customer $customer */
        $customer = $request->user();

        if ($address->customer_id !== $customer->id) {
            abort(403);
        }

        $customer->addresses()->update(['default' => false]);
        $address->update(['default' => true]);

        return new AddressResource($address);
    }
}
