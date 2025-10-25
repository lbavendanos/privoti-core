<?php

declare(strict_types=1);

use App\Actions\Product\UpdateProductVariantsAction;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('updates product variants for a product', function () {
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

    $variant1 = ProductVariant::factory()->for($product)->create([
        'name' => 'Variant 1',
        'price' => 19.99,
        'quantity' => 10,
    ]);
    $variant1->values()->attach([
        /** @phpstan-ignore-next-line */
        ProductOptionValue::query()->where('value', 'Small')->first()->id,
        /** @phpstan-ignore-next-line */
        ProductOptionValue::query()->where('value', 'Red')->first()->id,
    ]);

    $variant2 = ProductVariant::factory()->for($product)->create([
        'name' => 'Variant 2',
        'price' => 29.99,
        'quantity' => 5,
    ]);
    $variant2->values()->attach([
        /** @phpstan-ignore-next-line */
        ProductOptionValue::query()->where('value', 'Large')->first()->id,
        /** @phpstan-ignore-next-line */
        ProductOptionValue::query()->where('value', 'Blue')->first()->id,
    ]);

    $attributes = [
        [
            'id' => $variant1->id,
            'name' => 'Updated Variant 1',
            'price' => 24.99,
            'quantity' => 15,
            'options' => [
                ['value' => 'Medium'],
                ['value' => 'Green'],
            ],
        ],
        [
            'id' => $variant2->id,
            'name' => 'Updated Variant 2',
            'price' => 34.99,
            'quantity' => 8,
            'options' => [
                ['value' => 'Small'],
                ['value' => 'Red'],
            ],
        ],
    ];

    /** @var UpdateProductVariantsAction $action */
    $action = app(UpdateProductVariantsAction::class);
    $updatedVariantCollection = $action->handle($product, $attributes);

    expect($updatedVariantCollection)->toHaveCount(2);
    expect($updatedVariantCollection)->each->toBeInstanceOf(ProductVariant::class);

    $updatedVariantCollection->each(function (ProductVariant $variant, $key) use ($attributes, $product) {
        expect($variant->product_id)->toBe($product->id);
        expect($variant->name)->toBe($attributes[$key]['name']);
        expect($variant->price)->toBe($attributes[$key]['price']);
        expect($variant->quantity)->toBe($attributes[$key]['quantity']);

        $expectedValues = collect($attributes[$key]['options'])->pluck('value')->toArray();
        $values = $variant->values->pluck('value')->toArray();

        expect($values)->toBe($expectedValues);
    });

});

it('throws an exception when variant id is missing', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    $attributes = [
        [
            // 'id' key is missing
            'name' => 'Variant 1',
            'price' => 19.99,
            'quantity' => 10,
            'options' => [
                ['value' => 'Small'],
                ['value' => 'Red'],
            ],
        ],
    ];

    /** @var UpdateProductVariantsAction $action */
    $action = app(UpdateProductVariantsAction::class);

    $action->handle($product, $attributes);
})->throws(InvalidArgumentException::class, 'The id attribute is required for updating variants.');

it('throws an exception when variant id does not exist', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    $attributes = [
        [
            'id' => 9999, // Non-existent ID
            'name' => 'Variant 1',
            'price' => 19.99,
            'quantity' => 10,
            'options' => [
                ['value' => 'Small'],
                ['value' => 'Red'],
            ],
        ],
    ];

    /** @var UpdateProductVariantsAction $action */
    $action = app(UpdateProductVariantsAction::class);

    $action->handle($product, $attributes);
})->throws(ModelNotFoundException::class);

it('updates product variants without changing options', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    $variant = ProductVariant::factory()->for($product)->create([
        'name' => 'Variant 1',
        'price' => 19.99,
        'quantity' => 10,
    ]);

    $attributes = [
        [
            'id' => $variant->id,
            'name' => 'Updated Variant 1',
            'price' => 24.99,
            'quantity' => 15,
            // No 'options' key provided
        ],
    ];

    /** @var UpdateProductVariantsAction $action */
    $action = app(UpdateProductVariantsAction::class);
    $updatedVariantCollection = $action->handle($product, $attributes);

    expect($updatedVariantCollection)->toHaveCount(1);
    expect($updatedVariantCollection->first())->toBeInstanceOf(ProductVariant::class);

    /** @var ProductVariant $updatedVariant */
    $updatedVariant = $updatedVariantCollection->first();
    expect($updatedVariant->name)->toBe('Updated Variant 1');
    expect($updatedVariant->price)->toBe(24.99);
    expect($updatedVariant->quantity)->toBe(15);
    expect($updatedVariant->values->toArray())->toBe($variant->values->toArray());
});

it('handles empty variants attributes', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    $updatedVariantCollection = app(UpdateProductVariantsAction::class)->handle($product, []);

    expect($updatedVariantCollection)->toBeEmpty();
});
