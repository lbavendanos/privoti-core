<?php

declare(strict_types=1);

use App\Actions\Product\DeleteProductAction;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;

it('deletes a product', function () {
    $product = Product::factory()->create();

    $variant1 = ProductVariant::factory()->for($product)->create();
    $variant2 = ProductVariant::factory()->for($product)->create();

    $option1 = ProductOption::factory()->for($product)->create();
    $option2 = ProductOption::factory()->for($product)->create();

    $value1 = ProductOptionValue::factory()->for($option1, 'option')->create();
    $value2 = ProductOptionValue::factory()->for($option2, 'option')->create();

    $media1 = ProductMedia::factory()->for($product)->create();
    $media2 = ProductMedia::factory()->for($product)->create();

    app(DeleteProductAction::class)->handle($product);

    expect(Product::query()->find($product->id))->toBeNull();
    expect(ProductVariant::query()->find($variant1->id))->toBeNull();
    expect(ProductVariant::query()->find($variant2->id))->toBeNull();
    expect(ProductOption::query()->find($option1->id))->toBeNull();
    expect(ProductOption::query()->find($option2->id))->toBeNull();
    expect(ProductOptionValue::query()->find($value1->id))->toBeNull();
    expect(ProductOptionValue::query()->find($value2->id))->toBeNull();
    expect(ProductMedia::query()->find($media1->id))->toBeNull();
    expect(ProductMedia::query()->find($media2->id))->toBeNull();
});
