<?php

declare(strict_types=1);

use App\Actions\Product\SyncProductOptionValuesAction;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;

it('syncs product option values for a product option', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    /** @var ProductOption $option */
    $option = ProductOption::factory()->for($product)->create();

    $existingValue1 = ProductOptionValue::factory()->for($option, 'option')->create(['value' => 'Small']);
    $existingValue2 = ProductOptionValue::factory()->for($option, 'option')->create(['value' => 'Medium']);

    $attributes = ['Medium', 'Large', 'Extra Large'];

    /** @var SyncProductOptionValuesAction $action */
    $action = app(SyncProductOptionValuesAction::class);
    /** array{attached: list<int>, detached: list<int>, updated: list<int>} $syncedValues */
    $syncedValues = $action->handle($option, $attributes);

    expect($syncedValues['attached'])->toHaveCount(2);
    expect($syncedValues['detached'])->toHaveCount(1);
    expect($syncedValues['detached'])->toContain($existingValue1->id);
    expect($syncedValues['updated'])->toHaveCount(0);
});

it('handles empty option values attributes by detaching all values', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    /** @var ProductOption $option */
    $option = ProductOption::factory()->for($product)->create();

    $existingValue1 = ProductOptionValue::factory()->for($option, 'option')->create(['value' => 'Small']);
    $existingValue2 = ProductOptionValue::factory()->for($option, 'option')->create(['value' => 'Medium']);

    $attributes = [];

    /** @var SyncProductOptionValuesAction $action */
    $action = app(SyncProductOptionValuesAction::class);
    /** array{attached: list<int>, detached: list<int>, updated: list<int>} $syncedValues */
    $syncedValues = $action->handle($option, $attributes);

    expect($syncedValues['attached'])->toHaveCount(0);
    expect($syncedValues['detached'])->toHaveCount(2);
    expect($syncedValues['detached'])->toContain($existingValue1->id);
    expect($syncedValues['detached'])->toContain($existingValue2->id);
    expect($syncedValues['updated'])->toHaveCount(0);
});

it('handles all new option values attributes by attaching all values', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    /** @var ProductOption $option */
    $option = ProductOption::factory()->for($product)->create();

    $attributes = ['Small', 'Medium', 'Large'];

    /** @var SyncProductOptionValuesAction $action */
    $action = app(SyncProductOptionValuesAction::class);
    /** array{attached: list<int>, detached: list<int>, updated: list<int>} $syncedValues */
    $syncedValues = $action->handle($option, $attributes);

    expect($syncedValues['attached'])->toHaveCount(3);
    expect($syncedValues['detached'])->toHaveCount(0);
    expect($syncedValues['updated'])->toHaveCount(0);
});

it('does not change anything when attributes match existing values', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    /** @var ProductOption $option */
    $option = ProductOption::factory()->for($product)->create();

    $existingValue1 = ProductOptionValue::factory()->for($option, 'option')->create(['value' => 'Small']);
    $existingValue2 = ProductOptionValue::factory()->for($option, 'option')->create(['value' => 'Medium']);

    $attributes = ['Small', 'Medium'];

    /** @var SyncProductOptionValuesAction $action */
    $action = app(SyncProductOptionValuesAction::class);
    /** array{attached: list<int>, detached: list<int>, updated: list<int>} $syncedValues */
    $syncedValues = $action->handle($option, $attributes);

    expect($syncedValues['attached'])->toHaveCount(0);
    expect($syncedValues['detached'])->toHaveCount(0);
    expect($syncedValues['updated'])->toHaveCount(0);
});

it('handles duplicate values in attributes by ignoring duplicates', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    /** @var ProductOption $option */
    $option = ProductOption::factory()->for($product)->create();

    $existingValue1 = ProductOptionValue::factory()->for($option, 'option')->create(['value' => 'Small']);
    $existingValue2 = ProductOptionValue::factory()->for($option, 'option')->create(['value' => 'Medium']);

    $attributes = ['Medium', 'Large', 'Large', 'Extra Large', 'Medium'];

    /** @var SyncProductOptionValuesAction $action */
    $action = app(SyncProductOptionValuesAction::class);
    /** array{attached: list<int>, detached: list<int>, updated: list<int>} $syncedValues */
    $syncedValues = $action->handle($option, $attributes);

    expect($syncedValues['attached'])->toHaveCount(2);
    expect($syncedValues['detached'])->toHaveCount(1);
    expect($syncedValues['detached'])->toContain($existingValue1->id);
    expect($syncedValues['updated'])->toHaveCount(0);
});
