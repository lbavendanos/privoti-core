<?php

declare(strict_types=1);

namespace App\Actions\CustomerAddress;

use App\Actions\Common\ApplyCreatedAtFilterAction;
use App\Actions\Common\ApplySortFilterAction;
use App\Actions\Common\ApplyUpdatedAtFilterAction;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

final readonly class GetCustomerAddressesAction
{
    /**
     * @param  ApplyCreatedAtFilterAction<CustomerAddress>  $createdAtFilter
     * @param  ApplyUpdatedAtFilterAction<CustomerAddress>  $updatedAtFilter
     * @param  ApplySortFilterAction<CustomerAddress>  $sortFilter
     */
    public function __construct(
        private ApplyCreatedAtFilterAction $createdAtFilter,
        private ApplyUpdatedAtFilterAction $updatedAtFilter,
        private ApplySortFilterAction $sortFilter
    ) {
        //
    }

    /**
     * Builds a customer address query based on provided filters and ordering options.
     *
     * @param  array<string,mixed>  $filters
     * @return LengthAwarePaginator<int, CustomerAddress>
     */
    public function handle(Customer $customer, array $filters = []): LengthAwarePaginator
    {
        $query = CustomerAddress::query()->where('customer_id', $customer->id);

        if (Arr::has($filters, 'created_at')) {
            /** @var list<string> $dates */
            $dates = Arr::array($filters, 'created_at', []);
            $query = $this->createdAtFilter->handle($query, $dates);
        }

        if (Arr::has($filters, 'updated_at')) {
            /** @var list<string> $dates */
            $dates = Arr::array($filters, 'updated_at', []);
            $query = $this->updatedAtFilter->handle($query, $dates);
        }

        if (Arr::has($filters, 'order')) {
            $sort = explode(',', Arr::string($filters, 'order', 'id'));
            $query = $this->sortFilter->handle($query, $sort);
        }

        /** @phpstan-ignore-next-line */
        $perPage = (int) Arr::get($filters, 'per_page', 15);
        /** @phpstan-ignore-next-line */
        $page = (int) (Arr::get($filters, 'page', 1));

        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}
