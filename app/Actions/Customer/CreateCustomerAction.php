<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class CreateCustomerAction
{
    /**
     * Create a new customer.
     *
     * @param  array<string,mixed>  $attributes
     */
    public function handle(array $attributes): Customer
    {
        return DB::transaction(function () use ($attributes): Customer {
            $exists = Customer::query()
                ->where('email', $attributes['email'])
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'email' => ['The email has already been taken.'],
                ]);
            }

            return Customer::query()->create($attributes);
        });
    }
}
