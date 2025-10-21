<?php

declare(strict_types=1);

use App\Actions\Product\CreateProductOptionsAction;
use App\Models\Product;
use App\Models\ProductOption;

it('creates product options for a product', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    $attributes = [
        [
            'name' => 'Size',
            'values' => ['Small', 'Medium', 'Large'],
        ],
        [
            'name' => 'Color',
            'values' => ['Red', 'Blue', 'Green'],
        ],
    ];

    /** @var CreateProductOptionsAction $action */
    $action = app(CreateProductOptionsAction::class);
    $optionCollection = $action->handle($product, $attributes);

    expect($optionCollection)->toHaveCount(2);
    expect($optionCollection)->each->toBeInstanceOf(ProductOption::class);

    $optionCollection->each(function (ProductOption $option, $key) use ($attributes, $product) {
        expect($option->product_id)->toBe($product->id);
        expect($option->name)->toBe($attributes[$key]['name']);

        $expectedValues = $attributes[$key]['values'];
        $values = $option->values->pluck('value')->toArray();

        expect($values)->toBe($expectedValues);
    });
});

it('creates product options without values', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    $attributes = [
        ['name' => 'Material'],
        ['name' => 'Brand'],
    ];

    /** @var CreateProductOptionsAction $action */
    $action = app(CreateProductOptionsAction::class);
    $optionCollection = $action->handle($product, $attributes);

    expect($optionCollection)->toHaveCount(2);
    expect($optionCollection)->each->toBeInstanceOf(ProductOption::class);

    $optionCollection->each(function (ProductOption $option, $key) use ($attributes, $product) {
        expect($option->product_id)->toBe($product->id);
        expect($option->name)->toBe($attributes[$key]['name']);
        expect($option->values)->toBeEmpty();
    });
});

it('handles empty options attributes', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    $attributes = [];

    /** @var CreateProductOptionsAction $action */
    $action = app(CreateProductOptionsAction::class);
    $optionCollection = $action->handle($product, $attributes);

    expect($optionCollection)->toHaveCount(0);
});
