<?php

declare(strict_types=1);

use App\Actions\CustomerAddress\GetCustomerAddressAction;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('retrieves a customer address by model instance', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create();

    $fetched = (new GetCustomerAddressAction())->handle($customer, $address);

    expect($fetched)->toBeInstanceOf(CustomerAddress::class)
        ->and($fetched->id)->toBe($address->id)
        ->and($fetched->customer_id)->toBe($customer->id);
});

it('retrieves a customer address by id', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create();

    $fetched = (new GetCustomerAddressAction())->handle($customer->id, $address->id);

    expect($fetched)->toBeInstanceOf(CustomerAddress::class)
        ->and($fetched->id)->toBe($address->id)
        ->and($fetched->customer_id)->toBe($customer->id);
});

it('throws an exception if the customer address does not belong to the customer', function () {
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer1)->create();

    (new GetCustomerAddressAction())->handle($customer2, $address);
})->throws(ModelNotFoundException::class);

it('throws an exception if the customer address does not exist', function () {
    $customer = Customer::factory()->create();

    (new GetCustomerAddressAction())->handle($customer, 999);
})->throws(ModelNotFoundException::class);

it('throws an exception if the customer does not exist', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create();

    (new GetCustomerAddressAction())->handle(999, $address);
})->throws(ModelNotFoundException::class);

it('throws an exception if both customer and address do not exist', function () {
    (new GetCustomerAddressAction())->handle(999, 999);
})->throws(ModelNotFoundException::class);
