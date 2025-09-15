<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Actions\Common\ApplyCreatedAtFilterAction;
use App\Actions\Common\ApplyUpdatedAtFilterAction;
use App\Models\Customer;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;

final readonly class GetCustomersAction
{
    /**
     * @param  ApplyCreatedAtFilterAction<Customer>  $createdAtFilter
     * @param  ApplyUpdatedAtFilterAction<Customer>  $updatedAtFilter
     */
    public function __construct(
        private ApplyCreatedAtFilterAction $createdAtFilter,
        private ApplyUpdatedAtFilterAction $updatedAtFilter
    ) {
        //
    }

    /**
     * Builds a customer query based on provided filters and ordering options.
     *
     * @param  array<string,mixed>  $filters
     * @return AbstractPaginator<int, Customer>
     */
    public function handle(array $filters): AbstractPaginator
    {
        $query = Customer::query();

        $query->when(Arr::has($filters, 'name'), fn ($q) => $q->whereRaw("CONCAT(first_name, ' ', last_name) like ?", [sprintf('%%%s%%', Arr::string($filters, 'name'))]));
        $query->when(Arr::has($filters, 'account'), fn ($q) => $q->whereIn('account', Arr::array($filters, 'account')));

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

        $orders = explode(',', Arr::string($filters, 'order', 'id'));

        foreach ($orders as $order) {
            $direction = str_starts_with($order, '-') ? 'desc' : 'asc';
            $column = mb_ltrim($order, '-');

            if ($column === 'name') {
                $query->orderByRaw("CONCAT(first_name, ' ', last_name) ".$direction);
            } else {
                $query->orderBy($column, $direction);
            }
        }

        $perPage = Arr::integer($filters, 'per_page', 15);
        $page = Arr::integer($filters, 'page', 1);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}
