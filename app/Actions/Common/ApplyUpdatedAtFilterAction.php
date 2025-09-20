<?php

declare(strict_types=1);

namespace App\Actions\Common;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * @template TModel of Model
 */
final readonly class ApplyUpdatedAtFilterAction
{
    /**
     * Applies a updated_at date filter to the given query.
     *
     * @param  Builder<TModel>  $query
     * @param  list<string>  $dates
     * @return Builder<TModel>
     */
    public function handle(Builder $query, array $dates): Builder
    {
        if (blank($dates)) {
            return $query;
        }

        $appTimezone = Config::string('app.timezone');
        $coreTimezone = Config::string('core.timezone');

        $timestamps = array_map(fn (string $date): CarbonImmutable => CarbonImmutable::parse($date)->setTimezone($appTimezone), $dates);

        if (count($timestamps) === 2) {
            $start = $timestamps[0]->setTimezone($coreTimezone)->startOfDay()->setTimezone($appTimezone);
            $end = $timestamps[1]->setTimezone($coreTimezone)->endOfDay()->setTimezone($appTimezone);

            $query->whereBetween('updated_at', [$start, $end]);
        } elseif (count($timestamps) === 1) {
            $start = $timestamps[0]->setTimezone($coreTimezone)->startOfDay()->setTimezone($appTimezone);
            $end = $timestamps[0]->setTimezone($coreTimezone)->endOfDay()->setTimezone($appTimezone);

            $query->whereBetween('updated_at', [$start, $end]);
        }

        return $query;

    }
}
