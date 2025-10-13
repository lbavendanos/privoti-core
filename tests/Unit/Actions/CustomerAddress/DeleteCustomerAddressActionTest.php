<?php

declare(strict_types=1);

use App\Actions\CustomerAddress\DeleteCustomerAddressAction;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('deletes a customer address', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create();

    app(DeleteCustomerAddressAction::class)->handle($customer, $address);

    expect(CustomerAddress::query()->find($address->id))->toBeNull();
});

it('deletes a customer address using IDs', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create();

    app(DeleteCustomerAddressAction::class)->handle($customer->id, $address->id);

    expect(CustomerAddress::query()->find($address->id))->toBeNull();
});

it('throws an exception if the address does not belong to the customer', function () {
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer1)->create();

    app(DeleteCustomerAddressAction::class)->handle($customer2, $address);
})->throws(ModelNotFoundException::class);

it('throws an exception if the customer does not exist', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create();

    app(DeleteCustomerAddressAction::class)->handle(9999, $address);
})->throws(ModelNotFoundException::class);

it('throws an exception if the address does not exist', function () {
    $customer = Customer::factory()->create();

    app(DeleteCustomerAddressAction::class)->handle($customer, 9999);
})->throws(ModelNotFoundException::class);

it('throws an exception if both customer and address do not exist', function () {
    app(DeleteCustomerAddressAction::class)->handle(9999, 9999);
})->throws(ModelNotFoundException::class);
