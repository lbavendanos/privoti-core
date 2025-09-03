<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

final readonly class CreateCustomerAction
{
    /**
     * Create a new customer.
     *
     * @param  array<string,mixed>  $attributes
     */
    public function handle(array $attributes): Customer
    {
        return DB::transaction(fn (): Customer => Customer::query()->create($attributes));
    }
}
