<?php

declare(strict_types=1);

namespace App\Domains\Cms\Http\Controllers;

use App\Actions\Customer\CreateGuestCustomerAction;
use App\Actions\Customer\UpdateCustomerAction;
use App\Domains\Cms\Http\Requests\StoreCustomerRequest;
use App\Domains\Cms\Http\Requests\UpdateCustomerRequest;
use App\Domains\Cms\Http\Resources\CustomerCollection;
use App\Domains\Cms\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

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

        $query->when($request->filled('name'), fn ($q) => $q->whereAny(['first_name', 'last_name'], 'like', sprintf('%%%s%%', $request->string('name')->value())));
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

        $orders = explode(',', $request->string('order', 'id')->value());

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
     * Delete customer and its related data.
     */
    private function deleteCustomer(Customer $customer): void
    {
        $customer->addresses();
        $customer->delete();
    }
}
