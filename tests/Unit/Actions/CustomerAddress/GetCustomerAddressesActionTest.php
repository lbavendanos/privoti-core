<?php

declare(strict_types=1);

use App\Actions\CustomerAddress\GetCustomerAddressesAction;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;

it('returns paginated customer addresses', function () {
    $customer = Customer::factory()->create();
    CustomerAddress::factory()->count(30)->for($customer)->create();

    /** @var LengthAwarePaginator<int, CustomerAddress> $result */
    $result = app(GetCustomerAddressesAction::class)->handle($customer);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(15)
        ->and($result->total())->toBe(30)
        ->and($result->currentPage())->toBe(1)
        ->and($result->lastPage())->toBe(2);
});

it('returns customer addresses filtered by a single :dataset date', function (string $field) {
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

    $customer = Customer::factory()->create();
    CustomerAddress::factory()
        ->count(count($mockDates))
        ->sequence(...array_map(fn ($date) => [$field => $date], $mockDates))
        ->for($customer)
        ->create();

    $filterDate = now()->subDays(5);

    /** @var LengthAwarePaginator<int, CustomerAddress> $result */
    $result = app(GetCustomerAddressesAction::class)->handle($customer, [
        $field => [$filterDate->toISOString()],
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(2);

    /** @var list<CarbonImmutable> $foundDates */
    $foundDates = $result->pluck($field)->all();

    expect($foundDates)->each->toBeBetween($filterDate->startOfDay(), $filterDate->endOfDay());
})->with(['created_at', 'updated_at']);

it('returns customer addresses filtered by :dataset date range', function (string $field) {
    /** @var List<CarbonImmutable> $mockDates */
    $mockDates = [
        now()->subDays(5),
        now()->subDays(4),
        now()->subDays(3),
        now()->subDays(2),
        now()->subDay(),
        now(),
    ];

    $customer = Customer::factory()->create();
    CustomerAddress::factory()
        ->count(count($mockDates))
        ->sequence(...array_map(fn ($date) => [$field => $date], $mockDates))
        ->for($customer)
        ->create();

    $filterDates = [now()->subDays(4), now()->subDays(2)];

    /** @var LengthAwarePaginator<int, CustomerAddress> $result */
    $result = app(GetCustomerAddressesAction::class)->handle($customer, [
        $field => array_map(fn ($date) => $date->toISOString(), $filterDates),
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(3);

    /** @var list<CarbonImmutable> $foundDates */
    $foundDates = $result->pluck($field)->all();

    expect($foundDates)->each->toBeBetween($filterDates[0]->startOfDay(), $filterDates[1]->endOfDay());
})->with(['created_at', 'updated_at']);

it('returns customer addresses ordered by :dataset ascending', function (string $field) {
    $customer = Customer::factory()->create();
    CustomerAddress::factory()->count(10)->for($customer)->create();

    /** @var LengthAwarePaginator<int, CustomerAddress> $result */
    $result = app(GetCustomerAddressesAction::class)->handle($customer, [
        'order' => $field,
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(10);

    /** @var list<string> $list */
    $list = $result->pluck($field)->all();

    $sortedList = $list;
    sort($sortedList);

    expect($list)->toBe($sortedList);
})->with(['id']);

it('returns customer addresses ordered by :dataset descending', function (string $field) {
    $customer = Customer::factory()->create();
    CustomerAddress::factory()->count(10)->for($customer)->create();

    /** @var LengthAwarePaginator<int, CustomerAddress> $result */
    $result = app(GetCustomerAddressesAction::class)->handle($customer, [
        'order' => sprintf('-%s', $field),
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(10);

    /** @var list<string> $list */
    $list = $result->pluck($field)->all();

    $sortedList = $list;
    rsort($sortedList);

    expect($list)->toBe($sortedList);
})->with(['id']);
