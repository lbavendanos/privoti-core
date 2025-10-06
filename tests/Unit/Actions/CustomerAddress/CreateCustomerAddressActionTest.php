<?php

declare(strict_types=1);

use App\Actions\CustomerAddress\CreateCustomerAddressAction;
use App\Exceptions\MaxAddressesLimitExceededException;
use App\Models\Customer;
use App\Models\CustomerAddress;

it('creates a customer address with basic attributes', function () {
    $customer = Customer::factory()->create();
    $attributes = [
        'first_name' => fake()->firstName(),
        'last_name' => fake()->lastName(),
        'phone' => '987654321',
        'address1' => 'Av. Natalia Zayas',
        'address2' => 'Dpto. 101',
        'district' => 'Lima',
        'city' => 'Lima',
        'state' => 'Lima',
    ];

    $address = (new CreateCustomerAddressAction())->handle($customer, $attributes);

    expect($address)
        ->toBeInstanceOf(CustomerAddress::class)
        ->and($address->first_name)->toBe($attributes['first_name'])
        ->and($address->last_name)->toBe($attributes['last_name'])
        ->and($address->phone)->toBe([
            'e164' => '+51987654321',
            'international' => '+51 987 654 321',
            'national' => '987 654 321',
            'mobile_dialing' => '987654321',
        ])
        ->and($address->address1)->toBe($attributes['address1'])
        ->and($address->address2)->toBe($attributes['address2'])
        ->and($address->district)->toBe($attributes['district'])
        ->and($address->city)->toBe($attributes['city'])
        ->and($address->state)->toBe($attributes['state'])
        ->and($address->customer_id)->toBe($customer->id);
});

it('creates the first address as default even if default is set to false', function () {
    $customer = Customer::factory()->create();
    $attributes = [
        'first_name' => fake()->firstName(),
        'last_name' => fake()->lastName(),
        'phone' => '987654321',
        'address1' => 'Av. Natalia Zayas',
        'address2' => 'Dpto. 101',
        'district' => 'Lima',
        'city' => 'Lima',
        'state' => 'Lima',
        'default' => false,
    ];

    $address = (new CreateCustomerAddressAction())->handle($customer, $attributes);

    expect($address->default)->toBeTrue();
});

it('throws an exception when creating more than the maximum allowed addresses for a customer', function () {
    $customer = Customer::factory()->create();
    CustomerAddress::factory()->count(CustomerAddress::MAX_ADDRESSES_PER_CUSTOMER)->for($customer)->create();

    $attributes = [
        'first_name' => fake()->firstName(),
        'last_name' => fake()->lastName(),
        'phone' => '987654321',
        'address1' => 'Av. Natalia Zayas',
        'address2' => 'Dpto. 101',
        'district' => 'Lima',
        'city' => 'Lima',
        'state' => 'Lima',
    ];

    (new CreateCustomerAddressAction())->handle($customer, $attributes);
})->throws(MaxAddressesLimitExceededException::class);
