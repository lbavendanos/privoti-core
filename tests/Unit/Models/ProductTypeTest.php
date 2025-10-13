<?php

declare(strict_types=1);

use App\Models\ProductType;

it('can create a product type', function () {
    $productType = ProductType::factory()->create();

    expect($productType)->toBeInstanceOf(ProductType::class);
    expect($productType->id)->toBeGreaterThan(0);
});

it('can have many products', function () {
    $productType = ProductType::factory()->hasProducts(5)->create();

    expect($productType->products)->toHaveCount(5);
});
