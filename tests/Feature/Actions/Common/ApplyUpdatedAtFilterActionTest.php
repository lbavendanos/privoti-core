<?php

declare(strict_types=1);

use App\Actions\Common\ApplyUpdatedAtFilterAction;
use App\Models\Customer;
use App\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @param  list<CarbonImmutable>  $dates
 */
function createModelsWithDates(string $model, array $dates): void
{
    /** @phpstan-ignore-next-line */
    $model::factory()
        ->count(count($dates))
        ->sequence(...array_map(fn ($date) => ['updated_at' => $date], $dates))
        ->create();
}

it('applies updated_at filter for :dataset', function (string $model) {
    /** @var list<CarbonImmutable> $mockDates */
    $mockDates = [now()->subDays(2), now()->subDay(), now()];

    createModelsWithDates($model, $mockDates);

    /** @var Builder<Model> $query */
    $query = $model::query();
    /** @var ApplyUpdatedAtFilterAction<Model> $action */
    $action = app(ApplyUpdatedAtFilterAction::class);
    /** @var list<string> $dates */
    $dates = [$mockDates[1]->toISOString()];
    $query = $action->handle($query, $dates);
    $result = $query->get();

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(1);

    /** @var Model $recordFound */
    $recordFound = $result->first();
    /** @var CarbonImmutable $updatedAtFound */
    /** @phpstan-ignore-next-line */
    $updatedAtFound = $recordFound->updated_at;

    expect($updatedAtFound)->toBeBetween($mockDates[1]->startOfDay(), $mockDates[1]->endOfDay());
})->with([
    'customers' => Customer::class,
    'products' => Product::class,
]);

it('applies updated_at range filter for :dataset', function (string $model) {
    /** @var list<CarbonImmutable> $mockDates */
    $mockDates = [now()->subDays(2), now()->subDay(), now()];

    createModelsWithDates($model, $mockDates);

    /** @var Builder<Model> $query */
    $query = $model::query();
    /** @var ApplyUpdatedAtFilterAction<Model> $action */
    $action = app(ApplyUpdatedAtFilterAction::class);
    /** @var list<string> $dates */
    $dates = [$mockDates[0]->toISOString(), $mockDates[1]->toISOString()];
    $query = $action->handle($query, $dates);
    $result = $query->get();

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(2);

    /** @var Model $recordFound */
    $recordFound = $result->first();
    /** @var CarbonImmutable $updatedAtFound */
    /** @phpstan-ignore-next-line */
    $updatedAtFound = $recordFound->updated_at;

    expect($updatedAtFound)->toBeBetween($mockDates[0]->startOfDay(), $mockDates[1]->endOfDay());
})->with([
    'customers' => Customer::class,
    'products' => Product::class,
]);

