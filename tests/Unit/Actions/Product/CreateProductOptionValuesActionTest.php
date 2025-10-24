<?php

declare(strict_types=1);

use App\Actions\Product\CreateProductOptionValuesAction;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;

it('creates product option values for a product option', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    /** @var ProductOption $option */
    $option = ProductOption::factory()->for($product)->create();

    $attributes = ['Small', 'Medium', 'Large'];

    /** @var CreateProductOptionValuesAction $action */
    $action = app(CreateProductOptionValuesAction::class);
    $valueCollection = $action->handle($option, $attributes);

    expect($valueCollection)->toHaveCount(3);
    expect($valueCollection)->each->toBeInstanceOf(ProductOptionValue::class);

    $values = $valueCollection->pluck('value')->toArray();
    expect($values)->toBe($attributes);
});

it('creates product option values with an empty array', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    /** @var ProductOption $option */
    $option = ProductOption::factory()->for($product)->create();

    $attributes = [];

    /** @var CreateProductOptionValuesAction $action */
    $action = app(CreateProductOptionValuesAction::class);
    $valueCollection = $action->handle($option, $attributes);

    expect($valueCollection)->toHaveCount(0);
});

it('creates product option values with duplicate values', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    /** @var ProductOption $option */
    $option = ProductOption::factory()->for($product)->create();

    $attributes = ['Small', 'Medium', 'Large', 'Medium', 'Small'];

    /** @var CreateProductOptionValuesAction $action */
    $action = app(CreateProductOptionValuesAction::class);
    $valueCollection = $action->handle($option, $attributes);

    expect($valueCollection)->toHaveCount(3);
    expect($valueCollection)->each->toBeInstanceOf(ProductOptionValue::class);

    $values = $valueCollection->pluck('value')->toArray();
    expect($values)->toBe(['Small', 'Medium', 'Large']);
});
