<?php

declare(strict_types=1);

use App\Actions\Product\UpdateProductOptionsAction;
use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('updates product options for a product', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    $option1 = ProductOption::factory()->for($product)->create(['name' => 'Size']);
    $option2 = ProductOption::factory()->for($product)->create(['name' => 'Color']);

    $attributes = [
        ['id' => $option1->id, 'name' => 'Updated Size'],
        ['id' => $option2->id, 'name' => 'Updated Color'],
    ];

    /** @var UpdateProductOptionsAction $action */
    $action = app(UpdateProductOptionsAction::class);
    $updatedOptionCollection = $action->handle($product, $attributes);

    expect($updatedOptionCollection)->toHaveCount(2);
    expect($updatedOptionCollection)->each->toBeInstanceOf(ProductOption::class);

    $updatedOptionCollection->each(function (ProductOption $option, $key) use ($attributes) {
        expect($option->name)->toBe($attributes[$key]['name']);
    });
});

it('handles empty options attributes', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    $updatedOptionCollection = app(UpdateProductOptionsAction::class)->handle($product, []);

    expect($updatedOptionCollection)->toBeEmpty();
});

it('throws an exception when id is missing in attributes', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    $attributes = [
        ['name' => 'Updated Size'],
    ];

    /** @var UpdateProductOptionsAction $action */
    $action = app(UpdateProductOptionsAction::class);

    $action->handle($product, $attributes);
})->throws(InvalidArgumentException::class, 'The id attribute is required for updating options.');

it('throws an exception when option id does not exist', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    $attributes = [
        ['id' => 9999, 'name' => 'Updated Size'],
    ];

    /** @var UpdateProductOptionsAction $action */
    $action = app(UpdateProductOptionsAction::class);

    $action->handle($product, $attributes);
})->throws(ModelNotFoundException::class);

it('updates product options with values', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    $option = ProductOption::factory()->for($product)->create(['name' => 'Size']);

    $attributes = [
        [
            'id' => $option->id,
            'name' => 'Updated Size',
            'values' => ['Extra Small', 'Extra Large'],
        ],
    ];

    /** @var UpdateProductOptionsAction $action */
    $action = app(UpdateProductOptionsAction::class);
    $updatedOptionCollection = $action->handle($product, $attributes);

    expect($updatedOptionCollection)->toHaveCount(1);
    expect($updatedOptionCollection->first())->toBeInstanceOf(ProductOption::class);

    /** @var ProductOption $updatedOption */
    $updatedOption = $updatedOptionCollection->first();
    expect($updatedOption->name)->toBe('Updated Size');

    $expectedValues = ['Extra Small', 'Extra Large'];
    $values = $updatedOption->values->pluck('value')->toArray();

    expect($values)->toBe($expectedValues);
});

it('handles options update without values', function () {
    /** @var Product $product */
    $product = Product::factory()->create();

    $option = ProductOption::factory()->for($product)->create(['name' => 'Material']);

    $attributes = [
        [
            'id' => $option->id,
            'name' => 'Updated Material',
        ],
    ];

    /** @var UpdateProductOptionsAction $action */
    $action = app(UpdateProductOptionsAction::class);
    $updatedOptionCollection = $action->handle($product, $attributes);

    expect($updatedOptionCollection)->toHaveCount(1);
    expect($updatedOptionCollection->first())->toBeInstanceOf(ProductOption::class);

    /** @var ProductOption $updatedOption */
    $updatedOption = $updatedOptionCollection->first();
    expect($updatedOption->name)->toBe('Updated Material');
    expect($updatedOption->values)->toBeEmpty();
});
