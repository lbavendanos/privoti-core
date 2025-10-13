<?php

declare(strict_types=1);

namespace App\Actions\Collection;

use App\Actions\Common\ApplyCreatedAtFilterAction;
use App\Actions\Common\ApplySortFilterAction;
use App\Actions\Common\ApplyUpdatedAtFilterAction;
use App\Models\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

final readonly class GetCollectionsAction
{
    /**
     * @param  ApplyCreatedAtFilterAction<Collection>  $createdAtFilter
     * @param  ApplyUpdatedAtFilterAction<Collection>  $updatedAtFilter
     * @param  ApplySortFilterAction<Collection>  $sortFilter
     */
    public function __construct(
        private ApplyCreatedAtFilterAction $createdAtFilter,
        private ApplyUpdatedAtFilterAction $updatedAtFilter,
        private ApplySortFilterAction $sortFilter
    ) {
        //
    }

    /**
     * Builds a collection query based on provided filters and ordering options.
     *
     * @param  array<string,mixed>  $filters
     * @return LengthAwarePaginator<int, Collection>
     */
    public function handle(array $filters = []): LengthAwarePaginator
    {
        $query = Collection::query();

        $query->when(Arr::has($filters, 'title'), fn ($q) => $q->where('title', 'like', sprintf('%%%s%%', Arr::string($filters, 'title'))));

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
