<?php

declare(strict_types=1);

use App\Actions\Customer\DeleteCustomerAction;
use App\Models\Customer;
use App\Models\CustomerAddress;

it('deletes a customer', function () {
    $customer = Customer::factory()->create();
    $address1 = CustomerAddress::factory()->create(['customer_id' => $customer->id]);
    $address2 = CustomerAddress::factory()->create(['customer_id' => $customer->id]);

    (new DeleteCustomerAction())->handle($customer);

    expect(Customer::query()->find($customer->id))->toBeNull();
    expect(CustomerAddress::query()->find($address1->id))->toBeNull();
    expect(CustomerAddress::query()->find($address2->id))->toBeNull();
});
