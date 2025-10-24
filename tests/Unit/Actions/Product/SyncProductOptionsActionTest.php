<?php

declare(strict_types=1);

use App\Actions\Product\SyncProductOptionsAction;
use App\Models\Product;
use App\Models\ProductOption;

it('syncs product options for a product', function () {
    /** @var Product $product */
    $product = Product::factory()->create();
    $existingOption1 = ProductOption::factory()->for($product)->create(['name' => 'Size']);
    $existingOption2 = ProductOption::factory()->for($product)->create(['name' => 'Color']);

    $attributes = [
        ['id' => $existingOption1->id, 'name' => 'Updated Size'],
        ['name' => 'Material'],
    ];

    /** @var SyncProductOptionsAction $action */
    $action = app(SyncProductOptionsAction::class);
    /** array{attached: list<int>, detached: list<int>, updated: list<int>} $syncedOptions */
    $syncedOptions = $action->handle($product, $attributes);

    expect($syncedOptions['attached'])->toHaveCount(1);
    expect($syncedOptions['detached'])->toHaveCount(1);
    expect($syncedOptions['detached'])->toContain($existingOption2->id);
    expect($syncedOptions['updated'])->toHaveCount(1);
});

it('handles empty options attributes by detaching all options', function () {
    /** @var Product $product */
    $product = Product::factory()->create();
    $existingOption1 = ProductOption::factory()->for($product)->create(['name' => 'Size']);
    $existingOption2 = ProductOption::factory()->for($product)->create(['name' => 'Color']);

    $attributes = [];

    /** @var SyncProductOptionsAction $action */
    $action = app(SyncProductOptionsAction::class);
    /** array{attached: list<int>, detached: list<int>, updated: list<int>} $syncedOptions */
    $syncedOptions = $action->handle($product, $attributes);

    expect($syncedOptions['attached'])->toHaveCount(0);
    expect($syncedOptions['detached'])->toHaveCount(2);
    expect($syncedOptions['detached'])->toContain($existingOption1->id);
    expect($syncedOptions['detached'])->toContain($existingOption2->id);
    expect($syncedOptions['updated'])->toHaveCount(0);
});

it('handles all new options attributes by attaching all options', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    $attributes = [
        ['name' => 'Size'],
        ['name' => 'Color'],
    ];

    /** @var SyncProductOptionsAction $action */
    $action = app(SyncProductOptionsAction::class);
    /** array{attached: list<int>, detached: list<int>, updated: list<int>} $syncedOptions */
    $syncedOptions = $action->handle($product, $attributes);

    expect($syncedOptions['attached'])->toHaveCount(2);
    expect($syncedOptions['detached'])->toHaveCount(0);
    expect($syncedOptions['updated'])->toHaveCount(0);
});

it('handles all existing options attributes by updating all options', function () {
    /** @var Product $product */
    $product = Product::factory()->create();
    $existingOption1 = ProductOption::factory()->for($product)->create(['name' => 'Size']);
    $existingOption2 = ProductOption::factory()->for($product)->create(['name' => 'Color']);

    $attributes = [
        ['id' => $existingOption1->id, 'name' => 'Updated Size'],
        ['id' => $existingOption2->id, 'name' => 'Updated Color'],
    ];

    /** @var SyncProductOptionsAction $action */
    $action = app(SyncProductOptionsAction::class);
    /** array{attached: list<int>, detached: list<int>, updated: list<int>} $syncedOptions */
    $syncedOptions = $action->handle($product, $attributes);

    expect($syncedOptions['attached'])->toHaveCount(0);
    expect($syncedOptions['detached'])->toHaveCount(0);
    expect($syncedOptions['updated'])->toHaveCount(2);
});
