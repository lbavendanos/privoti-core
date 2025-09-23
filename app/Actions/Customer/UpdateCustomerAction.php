<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

            if (filled($attributes['email'])) {
                $exists = Customer::query()
                    ->where('email', $attributes['email'])
                    ->where('id', '!=', $customer->id)
                    ->exists();

                if ($exists) {
                    throw ValidationException::withMessages([
                        'email' => ['The email has already been taken.'],
                    ]);
                }
            }

            $customer->update($attributes);

            return $customer;
        });
    }
}
