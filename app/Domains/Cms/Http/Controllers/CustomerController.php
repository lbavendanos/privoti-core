<?php

namespace App\Domains\Cms\Http\Controllers;

use App\Domains\Cms\Http\Resources\CustomerCollection;
use App\Domains\Cms\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Propaganistas\LaravelPhone\Rules\Phone;

class CustomerController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'name' => ['nullable', 'string'],
            'created_at' => ['nullable', 'array', 'max:2'],
            'created_at.*' => ['date'],
            'created_at.1' => ['nullable', 'after_or_equal:created_at.0'],
            'updated_at' => ['nullable', 'array', 'max:2'],
            'updated_at.*' => ['date'],
            'updated_at.1' => ['nullable', 'after_or_equal:updated_at.0'],
            'order' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer'],
        ]);

        $query = Customer::query();

        $query->with(['addresses']);

        $query->when($request->filled('name'), function ($q) use ($request) {
            $q->whereLike('first_name', "%{$request->input('name')}%")
                ->orWhereLike('last_name', "%{$request->input('name')}%");
        });

        $query->when($request->filled('account'), fn($q) => $q->where('account', $request->input('account')));

        $query->when($request->filled('created_at'), function ($q) use ($request) {
            $dates = $request->input('created_at');

            if (count($dates) === 2) {
                $q->createdBetween($dates);
            } elseif (count($dates) === 1) {
                $q->createdAt($dates[0]);
            }
        });

        $query->when($request->filled('updated_at'), function ($q) use ($request) {
            $dates = $request->input('updated_at');

            if (count($dates) === 2) {
                $q->updatedBetween($dates);
            } elseif (count($dates) === 1) {
                $q->updatedAt($dates[0]);
            }
        });

        $orders = explode(',', $request->input('order', 'id'));

        foreach ($orders as $order) {
            $direction = str_starts_with($order, '-') ? 'desc' : 'asc';
            $column = ltrim($order, '-');

            if ($column === 'name') {
                $query->orderByRaw("CONCAT(first_name, ' ', last_name) $direction");
            } else {
                $query->orderBy($column, $direction);
            }
        }

        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        return new CustomerCollection($query->paginate($perPage, ['*'], 'page', $page));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = array_merge(
            $this->customerRules(),
        );

        $request->validate($rules, ['phone' => 'The :attribute field must be a valid number.']);

        if ($request->missing('account')) {
            $request->merge(['account' => Customer::ACCOUNT_DEFAULT]);
        }

        $customer = Customer::create($request->all());

        return new CustomerResource($customer->load('addresses'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return new CustomerResource($customer->load('addresses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        //
    }

    /**
     * Customer rules.
     */
    private function customerRules(?Customer $customer = null)
    {
        return [
            'first_name' => $customer ? ['sometimes', 'required', 'string', 'max:255'] : ['required', 'string', 'max:255'],
            'last_name' => $customer ? ['sometimes', 'required', 'string', 'max:255'] : ['required', 'string', 'max:255'],
            'email' => $customer ? ['sometimes', 'required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('customers')->ignore($customer->id)->withoutTrashed()] : ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('customers')->withoutTrashed()],
            'phone' => ['nullable', 'string', (new Phone)->country([config('app.country_code')]), 'max:255'],
            'dob' => ['nullable', 'date'],
        ];
    }
}
