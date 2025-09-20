<?php

declare(strict_types=1);

use App\Actions\Customer\CreateCustomerAction;
use App\Models\Customer;

it('creates a customer with basic attributes', function () {
    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'dob' => '1990-01-01',
        'password' => 'password',
    ];

    $customer = (new CreateCustomerAction())->handle($attributes);

    expect($customer)
        ->toBeInstanceOf(Customer::class)
        ->and($customer->first_name)->toBe($attributes['first_name'])
        ->and($customer->last_name)->toBe($attributes['last_name'])
        ->and($customer->email)->toBe($attributes['email'])
        ->and($customer->dob)->toBe($attributes['dob'])
        ->and(password_verify('password', $customer->password))->toBeTrue();
});

it('creates a customer with phone number', function () {
    $attributes = [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
        'phone' => '987654321',
    ];

    $customer = (new CreateCustomerAction())->handle($attributes);

    expect($customer->phone)->toBe([
        'e164' => '+51987654321',
        'international' => '+51 987 654 321',
        'national' => '987 654 321',
        'mobile_dialing' => '987654321',
    ]);
});
