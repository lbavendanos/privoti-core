<?php

declare(strict_types=1);

use App\Actions\ProductType\GetProductTypesAction;
use App\Models\ProductType;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

it('returns paginated product types', function () {
    ProductType::factory()->count(30)->create();

    /** @var LengthAwarePaginator<int, ProductType> $result */
    $result = app(GetProductTypesAction::class)->handle();

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(15)
        ->and($result->total())->toBe(30)
        ->and($result->currentPage())->toBe(1)
        ->and($result->lastPage())->toBe(2);
});

it('returns product types filtered by name', function () {
    /** @var Collection<int, ProductType> $types */
    $types = ProductType::factory()->count(5)->create();
    /** @var ProductType $firstProductType */
    $firstProductType = $types->first();

    $result = app(GetProductTypesAction::class)->handle([
        'name' => $firstProductType->name,
    ]);

    expect($result)->not->toBeEmpty();

    /** @var ProductType $typeFound */
    $typeFound = $result->first();

    expect($typeFound->id)->toBe($firstProductType->id)
        ->and($typeFound->name)->toBe($firstProductType->name);
});

it('returns product types filtered by a single :dataset date', function (string $field) {
    /** @var List<CarbonImmutable> $mockDates */
    $mockDates = [
        now()->subDays(5),
        now()->subDays(5),
        now()->subDays(4),
        now()->subDays(3),
        now()->subDays(2),
        now()->subDay(),
        now(),
    ];

    ProductType::factory()
        ->count(count($mockDates))
        ->sequence(...array_map(fn ($date) => [$field => $date], $mockDates))
        ->create();

    $filterDate = now()->subDays(5);

    /** @var LengthAwarePaginator<int, ProductType> $result */
    $result = app(GetProductTypesAction::class)->handle([
        $field => [$filterDate->toISOString()],
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(2);

    /** @var list<CarbonImmutable> $foundDates */
    $foundDates = $result->pluck($field)->all();

    expect($foundDates)->each->toBeBetween($filterDate->startOfDay(), $filterDate->endOfDay());
})->with(['created_at', 'updated_at']);

it('returns product types filtered by :dataset date range', function (string $field) {
    /** @var List<CarbonImmutable> $mockDates */
    $mockDates = [
        now()->subDays(5),
        now()->subDays(5),
        now()->subDays(4),
        now()->subDays(3),
        now()->subDays(2),
        now()->subDay(),
        now(),
    ];

    ProductType::factory()
        ->count(count($mockDates))
        ->sequence(...array_map(fn ($date) => [$field => $date], $mockDates))
        ->create();

    $filterDates = [now()->subDays(4), now()->subDays(2)];

    /** @var LengthAwarePaginator<int, ProductType> $result */
    $result = app(GetProductTypesAction::class)->handle([
        $field => array_map(fn ($date) => $date->toISOString(), $filterDates),
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(3);

    /** @var list<CarbonImmutable> $foundDates */
    $foundDates = $result->pluck($field)->all();

    expect($foundDates)->each->toBeBetween($filterDates[0]->startOfDay(), $filterDates[1]->endOfDay());
})->with(['created_at', 'updated_at']);

it('returns product types ordered by :dataset ascending', function (string $field) {
    ProductType::factory()->count(10)->create();

    /** @var LengthAwarePaginator<int, ProductType> $result */
    $result = app(GetProductTypesAction::class)->handle([
        'order' => $field,
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(10);

    /** @var list<string> $list */
    $list = $result->pluck($field)->all();

    $sortedList = $list;
    sort($sortedList);

    expect($list)->toBe($sortedList);
})->with(['id', 'name']);

it('returns product types ordered by :dataset descending', function (string $field) {
    ProductType::factory()->count(10)->create();

    /** @var LengthAwarePaginator<int, ProductType> $result */
    $result = app(GetProductTypesAction::class)->handle([
        'order' => sprintf('-%s', $field),
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(10);

    /** @var list<string> $list */
    $list = $result->pluck($field)->all();

    $sortedList = $list;
    rsort($sortedList);

    expect($list)->toBe($sortedList);
})->with(['id', 'name']);
