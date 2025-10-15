<?php

declare(strict_types=1);

namespace App\Actions\CustomerAddress;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class GetCustomerAddressAction
{
    /**
     * Get the given customer address.
     */
    public function handle(Customer|int $customer, CustomerAddress|int $address): CustomerAddress
    {
        $customerId = $customer instanceof Customer ? $customer->id : $customer;
        $addressId = $address instanceof CustomerAddress ? $address->id : $address;

        $customerAddress = CustomerAddress::query()
            ->where('id', $addressId)
            ->where('customer_id', $customerId)
            ->first();

        if (! $customerAddress) {
            throw new ModelNotFoundException()->setModel(CustomerAddress::class, $addressId);
        }

        return $customerAddress;
    }
}
