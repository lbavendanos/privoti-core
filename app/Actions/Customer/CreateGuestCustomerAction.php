<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Models\Customer;

final readonly class CreateGuestCustomerAction
{
    /**
     * Create a new guest customer.
     *
     * @param  array<string,mixed>  $attributes
     */
    public function handle(array $attributes): Customer
    {
        $attributes = array_merge($attributes, [
            'account' => Customer::ACCOUNT_GUEST,
            'password' => null,
        ]);

        return app(CreateCustomerAction::class)->handle($attributes);
    }
}
