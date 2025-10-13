<?php

declare(strict_types=1);

use App\Actions\Vendor\GetVendorsAction;
use App\Models\Vendor;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

it('returns paginated vendors', function () {
    Vendor::factory()->count(30)->create();

    /** @var LengthAwarePaginator<int, Vendor> $result */
    $result = app(GetVendorsAction::class)->handle();

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(15)
        ->and($result->total())->toBe(30)
        ->and($result->currentPage())->toBe(1)
        ->and($result->lastPage())->toBe(2);
});

it('returns vendors filtered by name', function () {
    /** @var Collection<int, Vendor> $vendors */
    $vendors = Vendor::factory()->count(5)->create();
    /** @var Vendor $firstVendor */
    $firstVendor = $vendors->first();

    $result = app(GetVendorsAction::class)->handle([
        'name' => $firstVendor->name,
    ]);

    expect($result)->not->toBeEmpty();

    /** @var Vendor $vendorFound */
    $vendorFound = $result->first();

    expect($vendorFound->id)->toBe($firstVendor->id)
        ->and($vendorFound->name)->toBe($firstVendor->name);
});

it('returns vendors filtered by a single :dataset date', function (string $field) {
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

    Vendor::factory()
        ->count(count($mockDates))
        ->sequence(...array_map(fn ($date) => [$field => $date], $mockDates))
        ->create();

    $filterDate = now()->subDays(5);

    /** @var LengthAwarePaginator<int, Vendor> $result */
    $result = app(GetVendorsAction::class)->handle([
        $field => [$filterDate->toISOString()],
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(2);

    /** @var list<CarbonImmutable> $foundDates */
    $foundDates = $result->pluck($field)->all();

    expect($foundDates)->each->toBeBetween($filterDate->startOfDay(), $filterDate->endOfDay());
})->with(['created_at', 'updated_at']);

it('returns vendors filtered by :dataset date range', function (string $field) {
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

    Vendor::factory()
        ->count(count($mockDates))
        ->sequence(...array_map(fn ($date) => [$field => $date], $mockDates))
        ->create();

    $filterDates = [now()->subDays(4), now()->subDays(2)];

    /** @var LengthAwarePaginator<int, Vendor> $result */
    $result = app(GetVendorsAction::class)->handle([
        $field => array_map(fn ($date) => $date->toISOString(), $filterDates),
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(3);

    /** @var list<CarbonImmutable> $foundDates */
    $foundDates = $result->pluck($field)->all();

    expect($foundDates)->each->toBeBetween($filterDates[0]->startOfDay(), $filterDates[1]->endOfDay());
})->with(['created_at', 'updated_at']);

it('returns vendors ordered by :dataset ascending', function (string $field) {
    Vendor::factory()->count(10)->create();

    /** @var LengthAwarePaginator<int, Vendor> $result */
    $result = app(GetVendorsAction::class)->handle([
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

it('returns vendors ordered by :dataset descending', function (string $field) {
    Vendor::factory()->count(10)->create();

    /** @var LengthAwarePaginator<int, Vendor> $result */
    $result = app(GetVendorsAction::class)->handle([
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
