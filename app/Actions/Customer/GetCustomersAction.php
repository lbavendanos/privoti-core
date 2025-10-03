<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Actions\Common\ApplyCreatedAtFilterAction;
use App\Actions\Common\ApplySortFilterAction;
use App\Actions\Common\ApplyUpdatedAtFilterAction;
use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

final readonly class GetCustomersAction
{
    /**
     * @param  ApplyCreatedAtFilterAction<Customer>  $createdAtFilter
     * @param  ApplyUpdatedAtFilterAction<Customer>  $updatedAtFilter
     * @param  ApplySortFilterAction<Customer>  $sortFilter
     */
    public function __construct(
        private ApplyCreatedAtFilterAction $createdAtFilter,
        private ApplyUpdatedAtFilterAction $updatedAtFilter,
        private ApplySortFilterAction $sortFilter
    ) {
        //
    }

    /**
     * Builds a customer query based on provided filters and ordering options.
     *
     * @param  array<string,mixed>  $filters
     * @return LengthAwarePaginator<int, Customer>
     */
    public function handle(array $filters = []): LengthAwarePaginator
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

        if (Arr::has($filters, 'order')) {
            $sort = explode(',', Arr::string($filters, 'order', 'id'));
            $query = $this->sortFilter->handle($query, $sort, ['name' => "CONCAT(first_name, ' ', last_name)"]);
        }

        /** @phpstan-ignore-next-line */
        $perPage = (int) Arr::get($filters, 'per_page', 15);
        /** @phpstan-ignore-next-line */
        $page = (int) (Arr::get($filters, 'page', 1));

        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}
