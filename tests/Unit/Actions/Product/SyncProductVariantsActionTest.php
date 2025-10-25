<?php

declare(strict_types=1);

use App\Actions\Product\SyncProductVariantsAction;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;

it('syncs product variants for a product', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    $option1 = ProductOption::factory()->for($product)->create(['name' => 'Size']);
    $option2 = ProductOption::factory()->for($product)->create(['name' => 'Color']);

    $sizeValues = ProductOptionValue::factory()->count(3)->sequence(
        ['value' => 'Small'],
        ['value' => 'Medium'],
        ['value' => 'Large'],
    )->for($option1, 'option')->create();

    $colorValues = ProductOptionValue::factory()->count(3)->sequence(
        ['value' => 'Red'],
        ['value' => 'Blue'],
        ['value' => 'Green'],
    )->for($option2, 'option')->create();

    $variant1 = ProductVariant::factory()->for($product)->create([
        'name' => 'Variant 1',
        'price' => 19.99,
        'quantity' => 10,
    ]);
    $variant1->values()->attach([
        /** @phpstan-ignore-next-line */
        $sizeValues->firstWhere('value', 'Small')->id,
        /** @phpstan-ignore-next-line */
        $colorValues->firstWhere('value', 'Red')->id,
    ]);

    $variant2 = ProductVariant::factory()->for($product)->create([
        'name' => 'Variant 2',
        'price' => 29.99,
        'quantity' => 5,
    ]);
    $variant2->values()->attach([
        /** @phpstan-ignore-next-line */
        $sizeValues->firstWhere('value', 'Large')->id,
        /** @phpstan-ignore-next-line */
        $colorValues->firstWhere('value', 'Blue')->id,
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
            'name' => 'New Variant',
            'price' => 39.99,
            'quantity' => 20,
            'options' => [
                ['value' => 'Large'],
                ['value' => 'Red'],
            ],
        ],
    ];

    /** @var SyncProductVariantsAction $action */
    $action = app(SyncProductVariantsAction::class);
    /** array{attached: list<int>, detached: list<int>, updated: list<int>} $syncedVariants */
    $syncedVariants = $action->handle($product, $attributes);

    expect($syncedVariants['attached'])->toHaveCount(1);
    expect($syncedVariants['detached'])->toHaveCount(1);
    expect($syncedVariants['detached'])->toContain($variant2->id);
    expect($syncedVariants['updated'])->toHaveCount(1);
});

it('handles empty variants attributes by detaching all variants', function () {
    /** @var Product $product */
    $product = Product::factory()->create();
    $variant1 = ProductVariant::factory()->for($product)->create();
    $variant2 = ProductVariant::factory()->for($product)->create();

    $attributes = [];

    /** @var SyncProductVariantsAction $action */
    $action = app(SyncProductVariantsAction::class);
    /** array{attached: list<int>, detached: list<int>, updated: list<int>} $syncedVariants */
    $syncedVariants = $action->handle($product, $attributes);

    expect($syncedVariants['attached'])->toHaveCount(0);
    expect($syncedVariants['detached'])->toHaveCount(2);
    expect($syncedVariants['detached'])->toContain($variant1->id);
    expect($syncedVariants['detached'])->toContain($variant2->id);
    expect($syncedVariants['updated'])->toHaveCount(0);
});

it('handles all new variants attributes by attaching all variants', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    $option1 = ProductOption::factory()->for($product)->create(['name' => 'Size']);
    $option2 = ProductOption::factory()->for($product)->create(['name' => 'Color']);

    $sizeValues = ProductOptionValue::factory()->count(3)->sequence(
        ['value' => 'Small'],
        ['value' => 'Medium'],
        ['value' => 'Large'],
    )->for($option1, 'option')->create();

    $colorValues = ProductOptionValue::factory()->count(3)->sequence(
        ['value' => 'Red'],
        ['value' => 'Blue'],
        ['value' => 'Green'],
    )->for($option2, 'option')->create();

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

    /** @var SyncProductVariantsAction $action */
    $action = app(SyncProductVariantsAction::class);
    /** array{attached: list<int>, detached: list<int>, updated: list<int>} $syncedVariants */
    $syncedVariants = $action->handle($product, $attributes);

    expect($syncedVariants['attached'])->toHaveCount(2);
    expect($syncedVariants['detached'])->toHaveCount(0);
    expect($syncedVariants['updated'])->toHaveCount(0);
});

it('handles all existing variants attributes by updating all variants', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    $option1 = ProductOption::factory()->for($product)->create(['name' => 'Size']);
    $option2 = ProductOption::factory()->for($product)->create(['name' => 'Color']);

    $sizeValues = ProductOptionValue::factory()->count(3)->sequence(
        ['value' => 'Small'],
        ['value' => 'Medium'],
        ['value' => 'Large'],
    )->for($option1, 'option')->create();

    $colorValues = ProductOptionValue::factory()->count(3)->sequence(
        ['value' => 'Red'],
        ['value' => 'Blue'],
        ['value' => 'Green'],
    )->for($option2, 'option')->create();

    $variant1 = ProductVariant::factory()->for($product)->create([
        'name' => 'Variant 1',
        'price' => 19.99,
        'quantity' => 10,
    ]);
    $variant1->values()->attach([
        /** @phpstan-ignore-next-line */
        $sizeValues->firstWhere('value', 'Small')->id,
        /** @phpstan-ignore-next-line */
        $colorValues->firstWhere('value', 'Red')->id,
    ]);

    $variant2 = ProductVariant::factory()->for($product)->create([
        'name' => 'Variant 2',
        'price' => 29.99,
        'quantity' => 5,
    ]);
    $variant2->values()->attach([
        /** @phpstan-ignore-next-line */
        $sizeValues->firstWhere('value', 'Large')->id,
        /** @phpstan-ignore-next-line */
        $colorValues->firstWhere('value', 'Blue')->id,
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

    /** @var SyncProductVariantsAction $action */
    $action = app(SyncProductVariantsAction::class);
    /** array{attached: list<int>, detached: list<int>, updated: list<int>} $syncedVariants */
    $syncedVariants = $action->handle($product, $attributes);

    expect($syncedVariants['attached'])->toHaveCount(0);
    expect($syncedVariants['detached'])->toHaveCount(0);
    expect($syncedVariants['updated'])->toHaveCount(2);
});
