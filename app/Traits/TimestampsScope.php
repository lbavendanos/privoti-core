<?php

declare(strict_types=1);

namespace App\Traits;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

trait TimestampsScope
{
    /**
     * Scope a query to only include products created between the given dates.
     *
     * @param  Builder<Model>  $query
     * @param  array<int,string>  $dates
     */
    #[Scope]
    protected function createdBetween(Builder $query, array $dates): void
    {
        $appTimezone = Config::string('app.timezone');
        $coreTimezone = Config::string('core.timezone');

        $dates = array_map(fn (string $date): CarbonImmutable => CarbonImmutable::parse($date)->setTimezone($appTimezone), $dates);

        $start = $dates[0]->setTimezone($coreTimezone)->startOfDay()->setTimezone($appTimezone);
        $end = $dates[1]->setTimezone($coreTimezone)->endOfDay()->setTimezone($appTimezone);

        $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Scope a query to only include products created on a specific date.
     *
     * @param  Builder<Model>  $query
     */
    #[Scope]
    protected function createdAt(Builder $query, string $date): void
    {
        $appTimezone = Config::string('app.timezone');
        $coreTimezone = Config::string('core.timezone');

        $date = CarbonImmutable::parse($date)->setTimezone($appTimezone);

        $start = $date->setTimezone($coreTimezone)->startOfDay()->setTimezone($appTimezone);
        $end = $date->setTimezone($coreTimezone)->endOfDay()->setTimezone($appTimezone);

        $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Scope a query to only include products updated between the given dates.
     *
     * @param  Builder<Model>  $query
     * @param  array<int,string>  $dates
     */
    #[Scope]
    protected function updatedBetween(Builder $query, array $dates): void
    {
        $appTimezone = Config::string('app.timezone');
        $coreTimezone = Config::string('core.timezone');

        $dates = array_map(fn (string $date): CarbonImmutable => CarbonImmutable::parse($date)->setTimezone($appTimezone), $dates);

        $start = $dates[0]->setTimezone($coreTimezone)->startOfDay()->setTimezone($appTimezone);
        $end = $dates[1]->setTimezone($coreTimezone)->endOfDay()->setTimezone($appTimezone);

        $query->whereBetween('updated_at', [$start, $end]);
    }

    /**
     * Scope a query to only include products updated on a specific date.
     *
     * @param  Builder<Model>  $query
     */
    #[Scope]
    protected function updatedAt(Builder $query, string $date): void
    {
        $appTimezone = Config::string('app.timezone');
        $coreTimezone = Config::string('core.timezone');

        $date = CarbonImmutable::parse($date)->setTimezone($appTimezone);

        $start = $date->setTimezone($coreTimezone)->startOfDay()->setTimezone($appTimezone);
        $end = $date->setTimezone($coreTimezone)->endOfDay()->setTimezone($appTimezone);

        $query->whereBetween('updated_at', [$start, $end]);
    }
}
