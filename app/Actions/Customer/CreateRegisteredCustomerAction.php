<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Models\Customer;

final readonly class CreateRegisteredCustomerAction
{
    /**
     * Create a new registered customer.
     *
     * @param  array<string,mixed>  $attributes
     */
    public function handle(array $attributes): Customer
    {
        $attributes = array_merge($attributes, [
            'account' => Customer::ACCOUNT_REGISTERED,
        ]);

        return app(CreateCustomerAction::class)->handle($attributes);
    }
}
