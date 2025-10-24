<?php

declare(strict_types=1);

use App\Actions\Product\UpdateProductMediaAction;
use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('updates product media for a product', function () {
    $product = Product::factory()->create();

    $media1 = ProductMedia::factory()->create(['product_id' => $product->id, 'rank' => 1]);
    $media2 = ProductMedia::factory()->create(['product_id' => $product->id, 'rank' => 2]);

    $attributes = [
        ['id' => $media1->id, 'rank' => 3],
        ['id' => $media2->id, 'rank' => 4],
    ];

    /** @var UpdateProductMediaAction $action */
    $action = app(UpdateProductMediaAction::class);
    $updatedMediaCollection = $action->handle($product, $attributes);

    expect($updatedMediaCollection)->toHaveCount(2);
    expect($updatedMediaCollection)->each->toBeInstanceOf(ProductMedia::class);

    $updatedMediaCollection->each(function (ProductMedia $media, $key) use ($attributes) {
        expect($media->rank)->toBe($attributes[$key]['rank']);
    });
});

it('throws an exception when id is missing in attributes', function () {
    $product = Product::factory()->create();

    $attributes = [
        ['rank' => 3],
    ];

    /** @var UpdateProductMediaAction $action */
    $action = app(UpdateProductMediaAction::class);

    $action->handle($product, $attributes);
})->throws(InvalidArgumentException::class, 'The id attribute is required for updating media.');

it('throws an exception when media id does not exist', function () {
    $product = Product::factory()->create();

    $attributes = [
        ['id' => 9999, 'rank' => 3],
    ];

    /** @var UpdateProductMediaAction $action */
    $action = app(UpdateProductMediaAction::class);

    $action->handle($product, $attributes);
})->throws(ModelNotFoundException::class);
