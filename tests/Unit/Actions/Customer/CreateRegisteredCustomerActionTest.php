<?php

declare(strict_types=1);

use App\Actions\Customer\CreateRegisteredCustomerAction;
use App\Models\Customer;

it('creates a registered customer', function () {
    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'm@example.com',
        'password' => 'password',
    ];

    $customer = (new CreateRegisteredCustomerAction())->handle($attributes);

    expect($customer)
        ->toBeInstanceOf(Customer::class)
        ->and($customer->account)->toBe(Customer::ACCOUNT_REGISTERED)
        ->and(password_verify('password', $customer->password))->toBeTrue();
});
