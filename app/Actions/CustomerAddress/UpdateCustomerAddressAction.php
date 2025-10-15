<?php

declare(strict_types=1);

namespace App\Actions\CustomerAddress;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class UpdateCustomerAddressAction
{
    /**
     * Update the given customer address.
     *
     * @param  array<string,mixed>  $attributes
     */
    public function handle(Customer|int $customer, CustomerAddress|int $address, array $attributes): CustomerAddress
    {
        $address = $address instanceof CustomerAddress ? $address : CustomerAddress::query()->findOrFail($address);
        $customer = $customer instanceof Customer ? $customer : Customer::query()->findOrFail($customer);

        if ($address->customer_id !== $customer->id) {
            throw new ModelNotFoundException()->setModel(CustomerAddress::class, $address->id);
        }

        return DB::transaction(function () use ($customer, $address, $attributes): CustomerAddress {
            if (Arr::has($attributes, 'default')) {
                if ($attributes['default'] === true) {
                    $customer->addresses()
                        ->whereNot('id', $address->id)
                        ->update(['default' => false]);

                    $attributes['default'] = true;
                } elseif ($attributes['default'] === false) {
                    $hasDefaultAddress = $customer->addresses()
                        ->where('default', true)
                        ->whereNot('id', $address->id)
                        ->exists();

                    if (! $hasDefaultAddress) {
                        $attributes['default'] = true;
                    }
                }
            }

            $address->update($attributes);

            return $address;
        });
    }
}
