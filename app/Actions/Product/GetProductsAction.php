<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Actions\Common\ApplyCreatedAtFilterAction;
use App\Actions\Common\ApplySortFilterAction;
use App\Actions\Common\ApplyUpdatedAtFilterAction;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

final readonly class GetProductsAction
{
    /**
     * @param  ApplyCreatedAtFilterAction<Product>  $createdAtFilter
     * @param  ApplyUpdatedAtFilterAction<Product>  $updatedAtFilter
     * @param  ApplySortFilterAction<Product>  $sortFilter
     */
    public function __construct(
        private ApplyCreatedAtFilterAction $createdAtFilter,
        private ApplyUpdatedAtFilterAction $updatedAtFilter,
        private ApplySortFilterAction $sortFilter
    ) {
        //
    }

    /**
     * Builds a product pagination based on the provided filters.
     *
     * @param  array<string,mixed>  $filters
     * @return LengthAwarePaginator<int, Product>
     */
    public function handle(array $filters = []): LengthAwarePaginator
    {
        $query = Product::query();

        $query->with([
            'category',
            'type',
            'vendor',
            'media',
            'collections',
            'options.values',
            'variants.values',
        ]);

        $query->when(Arr::has($filters, 'title'), fn ($q) => $q->whereLike('title', sprintf('%%%s%%', Arr::string($filters, 'title'))));
        $query->when(Arr::has($filters, 'status'), fn ($q) => $q->whereIn('status', Arr::array($filters, 'status')));
        $query->when(Arr::has($filters, 'type'), fn ($q) => $q->whereHas('type', fn ($q) => $q->whereIn('name', Arr::array($filters, 'type'))));
        $query->when(Arr::has($filters, 'vendor'), fn ($q) => $q->whereHas('vendor', fn ($q) => $q->whereIn('name', Arr::array($filters, 'vendor'))));

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
