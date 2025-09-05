<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

final readonly class DeleteCustomerAction
{
    /**
     * Delete a customer.
     */
    public function handle(Customer $customer): void
    {
        DB::transaction(function () use ($customer): void {
            $customer->addresses()->delete();
            $customer->delete();
        });
    }
}
