<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductOption;
use App\Models\ProductType;
use App\Models\Vendor;

it('can create a product', function () {
    $product = Product::factory()->create();

    expect($product)->toBeInstanceOf(Product::class);
    expect($product->id)->toBeGreaterThan(0);
});

it('can belong to a category', function () {
    $product = Product::factory()->forCategory()->create();

    expect($product->category)->not->toBeNull()
        ->toBeInstanceOf(ProductCategory::class);
});

it('can belong to a type', function () {
    $product = Product::factory()->forType()->create();

    expect($product->type)->not->toBeNull()
        ->toBeInstanceOf(ProductType::class);
});

it('can belong to a vendor', function () {
    $product = Product::factory()->forVendor()->create();

    expect($product->vendor)->not->toBeNull()
        ->toBeInstanceOf(Vendor::class);
});

it('can have many media', function () {
    $product = Product::factory()->hasMedia(5)->create();

    expect($product->media)->toHaveCount(5);
});

it('can have many options', function () {
    $product = Product::factory()->hasOptions(5)->create();

    expect($product->options)->toHaveCount(5);
});

it('can have many option values', function () {
    $product = Product::factory()
        ->has(ProductOption::factory()->count(3)->hasValues(4), 'options')
        ->create();

    expect($product->values)->toHaveCount(12);
});

it('can have many variants', function () {
    $product = Product::factory()->hasVariants(5)->create();

    expect($product->variants)->toHaveCount(5);
});

it('can belong to many collections', function () {
    $product = Product::factory()->hasCollections(5)->create();

    expect($product->collections)->toHaveCount(5);
});

it('can get thumbnail', function () {
    $product = Product::factory()->hasMedia(1)->create();

    expect($product->thumbnail)->not->toBeNull()
        ->toBeString()
        ->toBeUrl();
});

it('can get stock as sum of variants quantities', function () {
    $product = Product::factory()->hasVariants(3)->create();

    $expectedStock = $product->variants()->sum('quantity');

    expect($product->stock)->toBe($expectedStock);
});

it('can get tags as array', function () {
    $product = Product::factory()->create([
        'tags' => ['tag1', 'tag2', 'tag3'],
    ]);

    expect($product->tags)->toBe(['tag1', 'tag2', 'tag3']);
});
