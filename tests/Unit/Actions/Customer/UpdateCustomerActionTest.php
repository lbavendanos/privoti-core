<?php

declare(strict_types=1);

use App\Actions\Customer\UpdateCustomerAction;
use App\Models\Customer;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    /** @phpstan-ignore-next-line */
    $this->customer = Customer::factory()->create([
        'password' => 'password',
    ]);
});

it('updates a customer by model instance', function () {
    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'm@example.com',
        'dob' => fake()->date(),
        'password' => 'newpassword',
    ];

    /** @var TestCase $this */
    /** @phpstan-ignore-next-line */
    $updated = (new UpdateCustomerAction())->handle($this->customer, $attributes);

    expect($updated)->toBeInstanceOf(Customer::class)
        ->and($updated->first_name)->toBe($attributes['first_name'])
        ->and($updated->last_name)->toBe($attributes['last_name'])
        ->and($updated->email)->toBe($attributes['email'])
        ->and($updated->dob)->toBe($attributes['dob'])
        ->and(password_verify($attributes['password'], $updated->password))->toBeTrue();
});

it('updates a customer by id', function () {
    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'm@example.com',
        'dob' => fake()->date(),
        'password' => 'newpassword',
    ];

    /** @var TestCase $this */
    /** @phpstan-ignore-next-line */
    $updated = (new UpdateCustomerAction())->handle($this->customer->id, $attributes);

    expect($updated)->toBeInstanceOf(Customer::class)
        ->and($updated->first_name)->toBe($attributes['first_name'])
        ->and($updated->last_name)->toBe($attributes['last_name'])
        ->and($updated->email)->toBe($attributes['email'])
        ->and($updated->dob)->toBe($attributes['dob'])
        ->and(password_verify($attributes['password'], $updated->password))->toBeTrue();
});

it('updates a customer with phone number', function () {
    /** @var TestCase $this */
    /** @phpstan-ignore-next-line */
    $updated = (new UpdateCustomerAction())->handle($this->customer, [
        'phone' => '987654321',
    ]);

    expect($updated->phone)->toBe([
        'e164' => '+51987654321',
        'international' => '+51 987 654 321',
        'national' => '987 654 321',
        'mobile_dialing' => '987654321',
    ]);
});
