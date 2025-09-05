<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

final readonly class DeleteCustomersAction
{
    public function __construct(private DeleteCustomerAction $action)
    {
        //
    }

    /**
     * Delete multiple customers by IDs.
     *
     * @param  list<int>  $ids
     */
    public function handle(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        DB::transaction(function () use ($ids): void {
            Customer::query()
                ->whereIn('id', $ids)
                ->chunkById(100, function ($customers): void {
                    foreach ($customers as $customer) {
                        $this->action->handle($customer);
                    }
                });
        });
    }
}
