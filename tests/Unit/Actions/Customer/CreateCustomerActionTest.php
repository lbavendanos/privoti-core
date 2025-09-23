<?php

declare(strict_types=1);

use App\Actions\Customer\CreateCustomerAction;
use App\Models\Customer;
use Illuminate\Validation\ValidationException;

it('creates a customer with basic attributes', function () {
    $attributes = [
        'first_name' => fake()->firstName(),
        'last_name' => fake()->lastName(),
        'email' => fake()->email(),
        'dob' => fake()->date(),
    ];

    $customer = (new CreateCustomerAction())->handle($attributes);

    expect($customer)
        ->toBeInstanceOf(Customer::class)
        ->and($customer->first_name)->toBe($attributes['first_name'])
        ->and($customer->last_name)->toBe($attributes['last_name'])
        ->and($customer->email)->toBe($attributes['email'])
        ->and($customer->dob)->toBe($attributes['dob']);
});

it('creates a customer with phone number', function () {
    $attributes = [
        'first_name' => fake()->firstName(),
        'last_name' => fake()->lastName(),
        'email' => fake()->email(),
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

it('throws a validation exception if email is duplicated', function () {
    Customer::factory()->create([
        'email' => 'm@example.com',
    ]);

    (new CreateCustomerAction())->handle([
        'first_name' => fake()->firstName(),
        'last_name' => fake()->lastName(),
        'email' => 'm@example.com',
    ]);
})->throws(ValidationException::class, 'The email has already been taken.');
