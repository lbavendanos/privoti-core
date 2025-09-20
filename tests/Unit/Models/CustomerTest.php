<?php

declare(strict_types=1);

use App\Models\Customer;

it('can create a customer', function () {
    $customer = Customer::factory()->create();

    expect($customer)->toBeInstanceOf(Customer::class);
    expect($customer->id)->toBeGreaterThan(0);
});

it('can have many addresses', function () {
    $customer = Customer::factory()->hasAddresses(5)->create();

    expect($customer->addresses)->toHaveCount(5);
});

it('can format first name to uppercase first letter', function () {
    $customer = Customer::factory()->create([
        'first_name' => 'john doe',
    ]);

    expect($customer->first_name)->toBe('John Doe');
});

it('can format last name to uppercase first letter', function () {
    $customer = Customer::factory()->create([
        'last_name' => 'doe smith',
    ]);

    expect($customer->last_name)->toBe('Doe Smith');
});

it('can get full name', function () {
    $customer = Customer::factory()->create([
        'first_name' => 'john',
        'last_name' => 'doe',
    ]);

    expect($customer->name)->toBe('John Doe');
});

it('can format phone number', function () {
    $customer = Customer::factory()->create([
        'phone' => '987654321',
    ]);

    expect($customer->phone)->toBe([
        'e164' => '+51987654321',
        'international' => '+51 987 654 321',
        'national' => '987 654 321',
        'mobile_dialing' => '987654321',
    ]);
});
