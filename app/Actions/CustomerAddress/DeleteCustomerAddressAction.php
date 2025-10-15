<?php

declare(strict_types=1);

namespace App\Actions\CustomerAddress;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final readonly class DeleteCustomerAddressAction
{
    /**
     * Delete a customer address.
     */
    public function handle(Customer|int $customer, CustomerAddress|int $address): void
    {
        $address = $address instanceof CustomerAddress ? $address : CustomerAddress::query()->findOrFail($address);
        $customer = $customer instanceof Customer ? $customer : Customer::query()->findOrFail($customer);

        if ($address->customer_id !== $customer->id) {
            throw new ModelNotFoundException()->setModel(CustomerAddress::class, $address->id);
        }

        DB::transaction(function () use ($address): void {
            $address->delete();
        });
    }
}
