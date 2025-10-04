<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\CustomerAddress;

it('can create a customer address', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create();

    expect($address)
        ->toBeInstanceOf(CustomerAddress::class)
        ->and($address->id)->toBeGreaterThan(0);
});

it('can belong to a customer', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create();

    expect($address->customer)
        ->toBeInstanceOf(Customer::class)
        ->and($address->customer_id)->toEqual($customer->id);
});

it('can format first name to uppercase first letter', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()
        ->for($customer)
        ->create([
            'first_name' => 'john doe',
        ]);

    expect($address->first_name)->toBe('John Doe');
});

it('can format last name to uppercase first letter', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()
        ->for($customer)
        ->create([
        'last_name' => 'doe smith',
    ]);

    expect($address->last_name)->toBe('Doe Smith');
});

it('can format phone number', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()
        ->for($customer)
        ->create([
        'phone' => '987654321',
    ]);

    expect($address->phone)->toBe([
        'e164' => '+51987654321',
        'international' => '+51 987 654 321',
        'national' => '987 654 321',
        'mobile_dialing' => '987654321',
    ]);
});
