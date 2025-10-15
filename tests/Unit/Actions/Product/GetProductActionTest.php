<?php

declare(strict_types=1);

use App\Actions\Product\GetProductAction;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('retrieves a product by model instance', function () {
    $product = Product::factory()->create();

    $fetched = (new GetProductAction())->handle($product);

    expect($fetched)->toBeInstanceOf(Product::class)
        ->and($fetched->id)->toBe($product->id);
});

it('retrieves a product by id', function () {
    $product = Product::factory()->create();

    $fetched = (new GetProductAction())->handle($product->id);

    expect($fetched)->toBeInstanceOf(Product::class)
        ->and($fetched->id)->toBe($product->id);
});

it('throws an exception if the product does not exist', function () {
    (new GetProductAction())->handle(999);
})->throws(ModelNotFoundException::class);

it('loads the necessary relationships', function () {
    $product = Product::factory()->create();

    $fetched = (new GetProductAction())->handle($product);

    expect($fetched->relationLoaded('category'))->toBeTrue()
        ->and($fetched->relationLoaded('type'))->toBeTrue()
        ->and($fetched->relationLoaded('vendor'))->toBeTrue()
        ->and($fetched->relationLoaded('collections'))->toBeTrue()
        ->and($fetched->relationLoaded('media'))->toBeTrue()
        ->and($fetched->relationLoaded('options'))->toBeTrue()
        ->and($fetched->relationLoaded('variants'))->toBeTrue();
});
