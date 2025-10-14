<?php

declare(strict_types=1);

use App\Actions\Product\GetProductsAction;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Vendor;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

it('returns paginated products', function () {
    Product::factory()->count(30)->create();

    /** @var LengthAwarePaginator<int, Product> $result */
    $result = app(GetProductsAction::class)->handle();

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(15)
        ->and($result->total())->toBe(30)
        ->and($result->currentPage())->toBe(1)
        ->and($result->lastPage())->toBe(2);
});

it('returns products filtered by title', function () {
    /** @var Collection<int, Product> $products */
    $products = Product::factory()->count(5)->create();
    /** @var Product $firstProduct */
    $firstProduct = $products->first();

    $result = app(GetProductsAction::class)->handle([
        'title' => $firstProduct->title,
    ]);

    expect($result)->not->toBeEmpty();

    /** @var Product $productFound */
    $productFound = $result->first();

    expect($productFound->id)->toBe($firstProduct->id)
        ->and($productFound->title)->toBe($firstProduct->title);
});

it('returns products filtered by status', function () {
    Product::factory()->count(5)->create(['status' => 'draft']);
    Product::factory()->count(3)->create(['status' => 'active']);
    Product::factory()->count(1)->create(['status' => 'archived']);

    $result = app(GetProductsAction::class)->handle([
        'status' => ['draft', 'active'],
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->total())->toBe(8);
});

it('returns products filtered by type', function () {
    /** @var Collection<int, Product> $products */
    $products = Product::factory()->count(5)->forType()->create();
    /** @var Product $firstProduct */
    $firstProduct = $products->first();
    /** @var ProductType $type */
    $type = $firstProduct->type;

    $result = app(GetProductsAction::class)->handle([
        'type' => [$type->name],
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->total())->toBe(5);
});

it('returns products filtered by vendor', function () {
    /** @var Collection<int, Product> $products */
    $products = Product::factory()->count(5)->forVendor()->create();
    /** @var Product $firstProduct */
    $firstProduct = $products->first();
    /** @var Vendor $vendor */
    $vendor = $firstProduct->vendor;

    $result = app(GetProductsAction::class)->handle([
        'vendor' => [$vendor->name],
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->total())->toBe(5);
});

it('returns products filtered by a single :dataset date', function (string $field) {
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

    Product::factory()
        ->count(count($mockDates))
        ->sequence(...array_map(fn ($date) => [$field => $date], $mockDates))
        ->create();

    $filterDate = now()->subDays(5);

    /** @var LengthAwarePaginator<int, Product> $result */
    $result = app(GetProductsAction::class)->handle([
        $field => [$filterDate->toISOString()],
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(2);

    /** @var list<CarbonImmutable> $foundDates */
    $foundDates = $result->pluck($field)->all();

    expect($foundDates)->each->toBeBetween($filterDate->startOfDay(), $filterDate->endOfDay());
})->with(['created_at', 'updated_at']);

it('returns products filtered by :dataset date range', function (string $field) {
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

    Product::factory()
        ->count(count($mockDates))
        ->sequence(...array_map(fn ($date) => [$field => $date], $mockDates))
        ->create();

    $filterDates = [now()->subDays(4), now()->subDays(2)];

    /** @var LengthAwarePaginator<int, Product> $result */
    $result = app(GetProductsAction::class)->handle([
        $field => array_map(fn ($date) => $date->toISOString(), $filterDates),
    ]);

    expect($result)->not->toBeEmpty()
        ->and($result->count())->toBe(3);

    /** @var list<CarbonImmutable> $foundDates */
    $foundDates = $result->pluck($field)->all();

    expect($foundDates)->each->toBeBetween($filterDates[0]->startOfDay(), $filterDates[1]->endOfDay());
})->with(['created_at', 'updated_at']);

it('returns products ordered by :dataset ascending', function (string $field) {
    Product::factory()->count(10)->create();

    /** @var LengthAwarePaginator<int, Product> $result */
    $result = app(GetProductsAction::class)->handle([
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

it('returns products ordered by :dataset descending', function (string $field) {
    Product::factory()->count(10)->create();

    /** @var LengthAwarePaginator<int, Product> $result */
    $result = app(GetProductsAction::class)->handle([
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
