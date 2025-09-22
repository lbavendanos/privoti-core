<?php

declare(strict_types=1);

use App\Actions\Customer\UpdateCustomerAction;
use App\Models\Customer;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    /** @phpstan-ignore-next-line */
    $this->customer = Customer::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'dob' => '1990-01-01',
        'password' => 'password',
    ]);
});

it('updates a customer by model instance', function () {
    /** @var TestCase $this */
    /** @phpstan-ignore-next-line */
    $updated = (new UpdateCustomerAction())->handle($this->customer, [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
        'dob' => '1992-02-02',
        'password' => 'newpassword',
    ]);

    expect($updated)->toBeInstanceOf(Customer::class)
        ->and($updated->first_name)->toBe('Jane')
        ->and($updated->last_name)->toBe('Smith')
        ->and($updated->email)->toBe('jane@example.com')
        ->and($updated->dob)->toBe('1992-02-02')
        ->and(password_verify('newpassword', $updated->password))->toBeTrue();
});

it('updates a customer by id', function () {
    /** @var TestCase $this */
    /** @phpstan-ignore-next-line */
    $updated = (new UpdateCustomerAction())->handle($this->customer->id, [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
        'dob' => '1992-02-02',
        'password' => 'newpassword',
    ]);

    expect($updated)->toBeInstanceOf(Customer::class)
        ->and($updated->first_name)->toBe('Jane')
        ->and($updated->last_name)->toBe('Smith')
        ->and($updated->email)->toBe('jane@example.com')
        ->and($updated->dob)->toBe('1992-02-02')
        ->and(password_verify('newpassword', $updated->password))->toBeTrue();
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
