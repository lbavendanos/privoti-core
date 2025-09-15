<?php

declare(strict_types=1);

use App\Actions\Customer\GetCustomersAction;
use App\Models\Customer;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

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

    /** @var AbstractPaginator<int, Customer> $result */
    $result = app(GetCustomersAction::class)->handle([
        'account' => [Customer::ACCOUNT_GUEST],
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(3);
});

it('returns customers filtered by registered account type', function () {
    Customer::factory()->guest()->count(5)->create();
    Customer::factory()->registered()->count(3)->create();

    /** @var AbstractPaginator<int, Customer> $result */
    $result = app(GetCustomersAction::class)->handle([
        'account' => [Customer::ACCOUNT_REGISTERED],
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(3);
});
