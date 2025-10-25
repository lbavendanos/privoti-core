<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;

it('can create a product option', function () {
    $product = Product::factory()->create();
    $productOption = ProductOption::factory()->for($product)->create();

    expect($productOption)->toBeInstanceOf(ProductOption::class);
    expect($productOption->id)->toBeGreaterThan(0);
});

it('belongs to a product', function () {
    $product = Product::factory()->create();
    $productOption = ProductOption::factory()->for($product)->create();

    expect($productOption->product)->toBeInstanceOf(Product::class);
    /** @phpstan-ignore-next-line */
    expect($productOption->product->id)->toBe($product->id);
});

it('can have many values', function () {
    $product = Product::factory()->create();
    $productOption = ProductOption::factory()->for($product)->create();
    ProductOptionValue::factory()->for($productOption, 'option')->count(2)->create();

    expect($productOption->values)->toHaveCount(2);
});
