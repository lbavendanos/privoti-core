<?php

declare(strict_types=1);

use App\Actions\Customer\CreateGuestCustomerAction;
use App\Models\Customer;

it('creates a guest customer', function () {
    $attributes = [
        'first_name' => fake()->firstName(),
        'last_name' => fake()->lastName(),
        'email' => fake()->email(),
    ];

    $customer = (new CreateGuestCustomerAction())->handle($attributes);

    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->account)->toBe(Customer::ACCOUNT_GUEST)
        ->and($customer->password)->toBeNull();
});
