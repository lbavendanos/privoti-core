<?php

declare(strict_types=1);

namespace App\Actions\CustomerAddress;

use App\Exceptions\MaxAddressesLimitExceededException;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Support\Facades\DB;

final readonly class CreateCustomerAddressAction
{
    /**
     * Create a new customer address.
     *
     * @param  array<string,mixed>  $attributes
     */
    public function handle(Customer $customer, array $attributes): CustomerAddress
    {
        return DB::transaction(function () use ($customer, $attributes): CustomerAddress {
            $numberOfAddresses = $customer->addresses()
                ->limit(CustomerAddress::MAX_ADDRESSES_PER_CUSTOMER)
                ->count();
            $hasMaxAddresses = $numberOfAddresses >= CustomerAddress::MAX_ADDRESSES_PER_CUSTOMER;
            $isFirstAddress = $numberOfAddresses === 0;

            if ($hasMaxAddresses) {
                throw MaxAddressesLimitExceededException::forCustomer(
                    CustomerAddress::MAX_ADDRESSES_PER_CUSTOMER
                );
            }

            $attributes['default'] = $isFirstAddress;

            return $customer->addresses()->create($attributes);
        });
    }
}
