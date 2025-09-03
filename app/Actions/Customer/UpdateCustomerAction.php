<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

final readonly class UpdateCustomerAction
{
    /**
     * Update the given customer.
     *
     * @param  array<string,mixed>  $attributes
     */
    public function handle(Customer|int $customer, array $attributes): Customer
    {
        return DB::transaction(function () use ($customer, $attributes): Customer {
            $customer = $customer instanceof Customer ? $customer : Customer::query()->findOrFail($customer);
            $customer->update($attributes);

            return $customer;
        });
    }
}
