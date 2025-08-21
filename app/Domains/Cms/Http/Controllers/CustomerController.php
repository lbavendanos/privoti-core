<?php

declare(strict_types=1);

namespace App\Domains\Cms\Http\Controllers;

use App\Domains\Cms\Http\Resources\CustomerCollection;
use App\Domains\Cms\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use Propaganistas\LaravelPhone\Rules\Phone;

final class CustomerController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): CustomerCollection
    {
        $request->validate([
            'name' => ['nullable', 'string'],
            'account' => ['nullable', 'array'],
            'account.*' => [Rule::in(Customer::ACCOUNT_LIST)],
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

        $query->when($request->filled('name'), fn ($q) => $q->whereAny(['first_name', 'last_name'], 'like', sprintf('%%%s%%', $request->string('name')->toString())));
        $query->when($request->filled('account'), fn ($q) => $q->whereIn('account', $request->array('account')));

        $query->when($request->filled('created_at'), function ($q) use ($request): void {
            /** @var array<int,string> $dates */
            $dates = $request->array('created_at');

            if (count($dates) === 2) {
                $q->createdBetween($dates);
            } elseif (count($dates) === 1) {
                $q->createdAt($dates[0]);
            }
        });

        $query->when($request->filled('updated_at'), function ($q) use ($request): void {
            /** @var array<int,string> $dates */
            $dates = $request->array('updated_at');

            if (count($dates) === 2) {
                $q->updatedBetween($dates);
            } elseif (count($dates) === 1) {
                $q->updatedAt($dates[0]);
            }
        });

        $orders = explode(',', $request->string('order', 'id')->toString());

        foreach ($orders as $order) {
            $direction = str_starts_with($order, '-') ? 'desc' : 'asc';
            $column = mb_ltrim($order, '-');

            if ($column === 'name') {
                $query->orderByRaw("CONCAT(first_name, ' ', last_name) ".$direction);
            } else {
                $query->orderBy($column, $direction);
            }
        }

        $perPage = $request->integer('per_page', 15);
        $page = $request->integer('page', 1);

        return new CustomerCollection($query->paginate($perPage, ['*'], 'page', $page));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): CustomerResource
    {
        $rules = array_merge(
            $this->customerRules(),
        );

        $request->validate($rules, ['phone' => 'The :attribute field must be a valid number.']);

        if ($request->missing('account')) {
            $request->merge(['account' => Customer::ACCOUNT_DEFAULT]);
        }

        /** @var array<string,mixed> $inputs */
        $inputs = $request->all();
        $customer = Customer::query()->create($inputs);

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
    public function update(Request $request, Customer $customer): CustomerResource
    {
        $rules = array_merge(
            $this->customerRules($customer),
        );

        $request->validate($rules, ['phone' => 'The :attribute field must be a valid number.']);

        /** @var array<string,mixed> $inputs */
        $inputs = $request->all();
        $customer->update($inputs);

        return new CustomerResource($customer->load('addresses'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer): Response
    {
        $this->deleteCustomer($customer);

        return response()->noContent();
    }

    /**
     * Remove multiple resources from storage.
     */
    public function bulkDestroy(Request $request): Response
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', Rule::exists('customers', 'id')->withoutTrashed()],
        ]);

        Customer::query()->whereIn('id', $request->input('ids'))
            ->chunkById(100, function ($customers): void {
                foreach ($customers as $customer) {
                    $this->deleteCustomer($customer);
                }
            });

        return response()->noContent();
    }

    /**
     * Customer rules.
     *
     * @return array<string,mixed>
     */
    private function customerRules(?Customer $customer = null): array
    {
        return [
            'first_name' => $customer instanceof Customer ? ['sometimes', 'required', 'string', 'max:255'] : ['required', 'string', 'max:255'],
            'last_name' => $customer instanceof Customer ? ['sometimes', 'required', 'string', 'max:255'] : ['required', 'string', 'max:255'],
            'email' => $customer instanceof Customer ? ['sometimes', 'required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('customers')->ignore($customer->id)->withoutTrashed()] : ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('customers')->withoutTrashed()],
            'phone' => ['nullable', 'string', (new Phone)->country([Config::string('core.country_code')]), 'max:255'],
            'dob' => ['nullable', 'date'],
        ];
    }

    /**
     * Delete customer and its related data.
     */
    private function deleteCustomer(Customer $customer): void
    {
        $customer->addresses();
        $customer->delete();
    }
}
