<?php

declare(strict_types=1);

use App\Actions\Customer\GetCustomersAction;
use App\Models\Customer;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

it('returns paginated customers', function () {
    Customer::factory()->count(30)->create();

    /** @var LengthAwarePaginator<int, Customer> $result */
    $result = app(GetCustomersAction::class)->handle();

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(15)
        ->and($result->total())->toBe(30)
        ->and($result->currentPage())->toBe(1)
        ->and($result->lastPage())->toBe(2);
});

it('returns customers filtered by name', function () {
    /** @var Collection<int, Customer> $customers */
    $customers = Customer::factory()->count(5)->create();
    /** @var Customer $firstCustomer */
    $firstCustomer = $customers->first();

    $result = app(GetCustomersAction::class)->handle([
        'name' => sprintf('%s %s', $firstCustomer->first_name, $firstCustomer->last_name),
    ]);

    expect($result)->not->toBeEmpty();

    /** @var Customer $customerFound */
    $customerFound = $result->first();

    expect($customerFound->id)->toBe($firstCustomer->id)
        ->and($customerFound->first_name)->toBe($firstCustomer->first_name)
        ->and($customerFound->last_name)->toBe($firstCustomer->last_name);
});

it('returns customers filtered by guest account type', function () {
    Customer::factory()->guest()->count(3)->create();
    Customer::factory()->registered()->count(5)->create();

    /** @var LengthAwarePaginator<int, Customer> $result */
    $result = app(GetCustomersAction::class)->handle([
        'account' => [Customer::ACCOUNT_GUEST],
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(3);
});

it('returns customers filtered by registered account type', function () {
    Customer::factory()->guest()->count(5)->create();
    Customer::factory()->registered()->count(3)->create();

    /** @var LengthAwarePaginator<int, Customer> $result */
    $result = app(GetCustomersAction::class)->handle([
        'account' => [Customer::ACCOUNT_REGISTERED],
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(3);
});

it('returns customers filtered by a single :dataset date', function (string $field) {
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

    Customer::factory()
        ->count(count($mockDates))
        ->sequence(...array_map(fn ($date) => [$field => $date], $mockDates))
        ->create();

    $filterDate = now()->subDays(5);

    /** @var LengthAwarePaginator<int, Customer> $result */
    $result = app(GetCustomersAction::class)->handle([
        $field => [$filterDate->toISOString()],
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(2);

    /** @var list<CarbonImmutable> $foundDates */
    $foundDates = $result->pluck($field)->all();

    expect($foundDates)->each->toBeBetween($filterDate->startOfDay(), $filterDate->endOfDay());
})->with(['created_at', 'updated_at']);

it('returns customers filtered by :dataset date range', function (string $field) {
    /** @var List<CarbonImmutable> $mockDates */
    $mockDates = [
        now()->subDays(5),
        now()->subDays(4),
        now()->subDays(3),
        now()->subDays(2),
        now()->subDay(),
        now(),
    ];

    Customer::factory()
        ->count(count($mockDates))
        ->sequence(...array_map(fn ($date) => [$field => $date], $mockDates))
        ->create();

    $filterDates = [now()->subDays(4), now()->subDays(2)];

    $result = app(GetCustomersAction::class)->handle([
        $field => array_map(fn ($date) => $date->toISOString(), $filterDates),
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(3);

    /** @var list<CarbonImmutable> $foundDates */
    $foundDates = $result->pluck($field)->all();

    expect($foundDates)->each->toBeBetween($filterDates[0]->startOfDay(), $filterDates[1]->endOfDay());
})->with(['created_at', 'updated_at']);

it('returns customers ordered by :dataset ascending', function (string $field) {
    Customer::factory()->count(10)->create();

    /** @var LengthAwarePaginator<int, Customer> $result */
    $result = app(GetCustomersAction::class)->handle([
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

it('returns customers ordered by :dataset descending', function (string $field) {
    Customer::factory()->count(10)->create();

    /** @var LengthAwarePaginator<int, Customer> $result */
    $result = app(GetCustomersAction::class)->handle([
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
