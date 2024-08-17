<?php

namespace App\Http\Controllers;

use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
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

        if ($numberOfAddresses === 5) {
            abort(403, 'You can not add more than 5 addresses.');
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

        $address->delete();

        return response()->noContent();
    }
}
