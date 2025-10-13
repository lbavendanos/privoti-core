<?php

declare(strict_types=1);

use App\Models\ProductCategory;

it('can create a product category', function () {
    $category = ProductCategory::factory()->create();

    expect($category)->toBeInstanceOf(ProductCategory::class);
    expect($category->id)->toBeGreaterThan(0);
});

it('can have a parent category', function () {
    $parentCategory = ProductCategory::factory()->create();
    $childCategory = ProductCategory::factory()->create(['parent_id' => $parentCategory->id]);

    expect($childCategory->parent)->toBeInstanceOf(ProductCategory::class);
    /** @phpstan-ignore-next-line */
    expect($childCategory->parent->id)->toEqual($parentCategory->id);
});

it('can have many child categories', function () {
    $parentCategory = ProductCategory::factory()->hasChildren(3)->create();

    expect($parentCategory->children)->toHaveCount(3);
});

it('can have many products', function () {
    $category = ProductCategory::factory()->hasProducts(4)->create();

    expect($category->products)->toHaveCount(4);
});
