<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductMedia;

it('can create a product media', function () {
    $product = Product::factory()->create();
    $productMedia = ProductMedia::factory()->for($product)->create();

    expect($productMedia)->toBeInstanceOf(ProductMedia::class);
    expect($productMedia->id)->toBeGreaterThan(0);
});

it('belongs to a product', function () {
    $product = Product::factory()->create();
    $productMedia = ProductMedia::factory()->for($product)->create();

    expect($productMedia->product)->toBeInstanceOf(Product::class);
    /** @phpstan-ignore-next-line */
    expect($productMedia->product->id)->toBe($product->id);
});

it('can get the name attribute from url', function () {
    $product = Product::factory()->create();
    $productMedia = ProductMedia::factory()->for($product)->create([
        'url' => 'https://example.com/images/product-image.jpg',
    ]);

    expect($productMedia->name)->toBe('product-image');
});

it('can get the type attribute from url', function () {
    $product = Product::factory()->create();
    $productMedia1 = ProductMedia::factory()->for($product)->create([
        'url' => 'https://example.com/images/product-image.png',
    ]);
    $productMedia2 = ProductMedia::factory()->for($product)->create([
        'url' => 'https://example.com/videos/product-video.mp4',
    ]);

    expect($productMedia1->type)->toBe('image');
    expect($productMedia2->type)->toBe('video');
});
