<?php

declare(strict_types=1);

use App\Actions\Customer\DeleteCustomerAction;
use App\Models\Customer;
use App\Models\CustomerAddress;

it('deletes a customer', function () {
    $customer = Customer::factory()->create();
    $address1 = CustomerAddress::factory()->for($customer)->create();
    $address2 = CustomerAddress::factory()->for($customer)->create();

    app(DeleteCustomerAction::class)->handle($customer);

    expect(Customer::query()->find($customer->id))->toBeNull();
    expect(CustomerAddress::query()->find($address1->id))->toBeNull();
    expect(CustomerAddress::query()->find($address2->id))->toBeNull();
});
