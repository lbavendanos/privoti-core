<?php

declare(strict_types=1);

namespace App\Actions\ProductType;

use App\Actions\Common\ApplyCreatedAtFilterAction;
use App\Actions\Common\ApplySortFilterAction;
use App\Actions\Common\ApplyUpdatedAtFilterAction;
use App\Models\ProductType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

final readonly class GetProductTypesAction
{
    /**
     * @param  ApplyCreatedAtFilterAction<ProductType>  $createdAtFilter
     * @param  ApplyUpdatedAtFilterAction<ProductType>  $updatedAtFilter
     * @param  ApplySortFilterAction<ProductType>  $sortFilter
     */
    public function __construct(
        private ApplyCreatedAtFilterAction $createdAtFilter,
        private ApplyUpdatedAtFilterAction $updatedAtFilter,
        private ApplySortFilterAction $sortFilter
    ) {
        //
    }

    /**
     * Builds a product type pagination based on the provided filters.
     *
     * @param  array<string,mixed>  $filters
     * @return LengthAwarePaginator<int, ProductType>
     */
    public function handle(array $filters = []): LengthAwarePaginator
    {
        $query = ProductType::query();

        $query->when(Arr::has($filters, 'name'), fn ($q) => $q->whereLike('name', sprintf('%%%s%%', Arr::string($filters, 'name'))));

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
        $page = (int) Arr::get($filters, 'page', 1);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}
