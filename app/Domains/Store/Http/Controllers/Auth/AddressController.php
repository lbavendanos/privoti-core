<?php

namespace App\Domains\Store\Http\Controllers\Auth;

use App\Domains\Store\Http\Controllers\Controller;
use App\Domains\Store\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    const ADDRESS_LIMIT = 5;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return AddressResource::collection($request->user()->addresses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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

        $numberOfAddresses = $request->user()->addresses()->count();

        if ($numberOfAddresses === self::ADDRESS_LIMIT) {
            abort(403, 'You can not add more than ' . self::ADDRESS_LIMIT . ' addresses.');
        }

        $request->merge(['default' => $numberOfAddresses === 0]);

        $address = $request->user()->addresses()->create($request->only([
            'first_name',
            'last_name',
            'phone',
            'address1',
            'address2',
            'district',
            'city',
            'state',
            'default',
        ]));

        return new AddressResource($address);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        return new AddressResource($address);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
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

        $address->update($request->only([
            'first_name',
            'last_name',
            'phone',
            'address1',
            'address2',
            'district',
            'city',
            'state',
        ]));

        return new AddressResource($address);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
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
    public function setDefault(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->user()->addresses()->update(['default' => false]);
        $address->update(['default' => true]);

        return new AddressResource($address);
    }
}
