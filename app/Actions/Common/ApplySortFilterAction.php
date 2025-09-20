<?php

declare(strict_types=1);

namespace App\Actions\Common;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
final readonly class ApplySortFilterAction
{
    /**
     * Applies a sorting filter to the given query.
     *
     * @param  Builder<TModel>  $query
     * @param  list<string>  $sort
     * @param  array<string, string>  $customSortColumns
     * @return Builder<TModel>
     */
    public function handle(Builder $query, array $sort, array $customSortColumns = []): Builder
    {
        foreach ($sort as $item) {
            $direction = str_starts_with($item, '-') ? 'desc' : 'asc';
            $column = mb_ltrim($item, '-');

            if (array_key_exists($column, $customSortColumns)) {
                $query->orderByRaw($customSortColumns[$column].' '.$direction);
            } else {
                $query->orderBy($column, $direction);
            }
        }

        return $query;
    }
}
