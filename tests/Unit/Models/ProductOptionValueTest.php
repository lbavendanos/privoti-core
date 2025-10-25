<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;

it('can create a product option value', function () {
    $productOption = ProductOption::factory()->for(Product::factory()->create())->create();
    $productOptionValue = ProductOptionValue::factory()->for($productOption, 'option')->create();

    expect($productOptionValue)->toBeInstanceOf(ProductOptionValue::class);
    expect($productOptionValue->id)->toBeGreaterThan(0);
});

it('belongs to a product option', function () {
    $productOption = ProductOption::factory()->for(Product::factory()->create())->create();
    $productOptionValue = ProductOptionValue::factory()->for($productOption, 'option')->create();

    expect($productOptionValue->option)->toBeInstanceOf(ProductOption::class);
    /** @phpstan-ignore-next-line */
    expect($productOptionValue->option->id)->toBe($productOption->id);
});

it('can belong to many product variants', function () {
    $productOption = ProductOption::factory()->for(Product::factory()->create())->create();
    $productOptionValue = ProductOptionValue::factory()->for($productOption, 'option')->create();
    $productVariant1 = ProductVariant::factory()->for(Product::factory()->create())->create();
    $productVariant2 = ProductVariant::factory()->for(Product::factory()->create())->create();

    $productOptionValue->variants()->attach([$productVariant1->id, $productVariant2->id]);

    expect($productOptionValue->variants)->toHaveCount(2);
});
