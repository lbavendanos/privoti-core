<?php

declare(strict_types=1);

use App\Actions\Collection\GetCollectionsAction;
use App\Models\Collection as CollectionModel;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

it('returns paginated collections', function () {
    CollectionModel::factory()->count(30)->create();

    /** @var LengthAwarePaginator<int, CollectionModel> $result */
    $result = app(GetCollectionsAction::class)->handle();

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(15)
        ->and($result->total())->toBe(30)
        ->and($result->currentPage())->toBe(1)
        ->and($result->lastPage())->toBe(2);
});

it('returns collections filtered by title', function () {
    /** @var Collection<int, CollectionModel> $collections */
    $collections = CollectionModel::factory()->count(5)->create();
    /** @var CollectionModel $firstCollection */
    $firstCollection = $collections->first();

    $result = app(GetCollectionsAction::class)->handle([
        'title' => $firstCollection->title,
    ]);

    expect($result)->not->toBeEmpty();

    /** @var CollectionModel $collectionFound */
    $collectionFound = $result->first();

    expect($collectionFound->id)->toBe($firstCollection->id)
        ->and($collectionFound->title)->toBe($firstCollection->title);
});

it('returns collections filtered by a single :dataset date', function (string $field) {
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

    CollectionModel::factory()
        ->count(count($mockDates))
        ->sequence(...array_map(fn ($date) => [$field => $date], $mockDates))
        ->create();

    $filterDate = now()->subDays(5);

    /** @var LengthAwarePaginator<int, CollectionModel> $result */
    $result = app(GetCollectionsAction::class)->handle([
        $field => [$filterDate->toISOString()],
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(2);

    /** @var list<CarbonImmutable> $foundDates */
    $foundDates = $result->pluck($field)->all();

    expect($foundDates)->each->toBeBetween($filterDate->startOfDay(), $filterDate->endOfDay());
})->with(['created_at', 'updated_at']);

it('returns collections filtered by :dataset date range', function (string $field) {
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

    CollectionModel::factory()
        ->count(count($mockDates))
        ->sequence(...array_map(fn ($date) => [$field => $date], $mockDates))
        ->create();

    $filterDates = [now()->subDays(4), now()->subDays(2)];

    /** @var LengthAwarePaginator<int, CollectionModel> $result */
    $result = app(GetCollectionsAction::class)->handle([
        $field => array_map(fn ($date) => $date->toISOString(), $filterDates),
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(3);

    /** @var list<CarbonImmutable> $foundDates */
    $foundDates = $result->pluck($field)->all();

    expect($foundDates)->each->toBeBetween($filterDates[0]->startOfDay(), $filterDates[1]->endOfDay());
})->with(['created_at', 'updated_at']);

it('returns collections ordered by :dataset ascending', function (string $field) {
    CollectionModel::factory()->count(10)->create();

    /** @var LengthAwarePaginator<int, CollectionModel> $result */
    $result = app(GetCollectionsAction::class)->handle([
        'order' => $field,
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(10);

    /** @var list<string> $list */
    $list = $result->pluck($field)->all();

    $sortedList = $list;
    sort($sortedList);

    expect($list)->toBe($sortedList);
})->with(['id', 'title']);

it('returns collections ordered by :dataset descending', function (string $field) {
    CollectionModel::factory()->count(10)->create();

    /** @var LengthAwarePaginator<int, CollectionModel> $result */
    $result = app(GetCollectionsAction::class)->handle([
        'order' => sprintf('-%s', $field),
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(10);

    /** @var list<string> $list */
    $list = $result->pluck($field)->all();

    $sortedList = $list;
    rsort($sortedList);

    expect($list)->toBe($sortedList);
})->with(['id', 'title']);
