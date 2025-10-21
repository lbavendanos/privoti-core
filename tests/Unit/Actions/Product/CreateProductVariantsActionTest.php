<?php

declare(strict_types=1);

use App\Actions\Product\CreateProductVariantsAction;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;

it('creates product variants for a product', function () {
    /** @var Product $product */
    $product = Product::factory()->create();
    $sizeOption = ProductOption::factory()->for($product)->create(['name' => 'Size']);
    ProductOptionValue::factory()->count(3)->sequence(
        ['value' => 'Small'],
        ['value' => 'Medium'],
        ['value' => 'Large'],
    )->for($sizeOption, 'option')->create();

    $colorOption = ProductOption::factory()->for($product)->create(['name' => 'Color']);
    ProductOptionValue::factory()->count(3)->sequence(
        ['value' => 'Red'],
        ['value' => 'Blue'],
        ['value' => 'Green'],
    )->for($colorOption, 'option')->create();

    $attributes = [
        [
            'name' => 'Variant 1',
            'price' => 19.99,
            'quantity' => 10,
            'options' => [
                ['value' => 'Small'],
                ['value' => 'Red'],
            ],
        ],
        [
            'name' => 'Variant 2',
            'price' => 29.99,
            'quantity' => 5,
            'options' => [
                ['value' => 'Large'],
                ['value' => 'Blue'],
            ],
        ],
    ];

    /** @var CreateProductVariantsAction $action */
    $action = app(CreateProductVariantsAction::class);
    $variantCollection = $action->handle($product, $attributes);

    expect($variantCollection)->toHaveCount(2);
    expect($variantCollection)->each->toBeInstanceOf(ProductVariant::class);

    $variantCollection->each(function (ProductVariant $variant, $key) use ($attributes, $product) {
        expect($variant->product_id)->toBe($product->id);
        expect($variant->name)->toBe($attributes[$key]['name']);
        expect($variant->price)->toBe($attributes[$key]['price']);
        expect($variant->quantity)->toBe($attributes[$key]['quantity']);

        $expectedValues = collect($attributes[$key]['options'])->pluck('value')->toArray();
        $values = $variant->values->pluck('value')->toArray();

        expect($values)->toBe($expectedValues);
    });
});

it('throws an exception when variant options are missing', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    $attributes = [
        [
            'name' => 'Variant 1',
            'price' => 19.99,
            'quantity' => 10,
            // 'options' key is missing
        ],
    ];

    /** @var CreateProductVariantsAction $action */
    $action = app(CreateProductVariantsAction::class);

    $action->handle($product, $attributes);
})->throws(InvalidArgumentException::class, 'Variant options are required.');
