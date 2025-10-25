<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;

it('can create a product variant', function () {
    $product = Product::factory()->create();
    $productVariant = ProductVariant::factory()->for($product)->create();

    expect($productVariant)->toBeInstanceOf(ProductVariant::class);
    expect($productVariant->id)->toBeGreaterThan(0);
});

it('belongs to a product', function () {
    $product = Product::factory()->create();
    $productVariant = ProductVariant::factory()->for($product)->create();

    expect($productVariant->product)->toBeInstanceOf(Product::class);
    /** @phpstan-ignore-next-line */
    expect($productVariant->product->id)->toBe($product->id);
});

it('can have many option values', function () {
    $product = Product::factory()->create();
    $productVariant = ProductVariant::factory()->for($product)->create();

    $productOption1 = ProductOption::factory()->for($product)->create();
    $productOptionValue1 = ProductOptionValue::factory()->for($productOption1, 'option')->create();

    $productOption2 = ProductOption::factory()->for($product)->create();
    $productOptionValue2 = ProductOptionValue::factory()->for($productOption2, 'option')->create();

    $productVariant->values()->attach([$productOptionValue1->id, $productOptionValue2->id]);

    expect($productVariant->values)->toHaveCount(2);
});
