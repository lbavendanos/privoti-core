<?php

declare(strict_types=1);

use App\Actions\Product\CreateProductAction;
use App\Models\Product;

it('creates a product with basic attributes', function () {
    $attributes = [
        'title' => 'Test Product',
        'subtitle' => 'This is a test product',
        'description' => 'Detailed description of the test product.',
        'tags' => ['test', 'product'],
        'metadata' => ['color' => 'red', 'size' => 'M'],
    ];

    /** @var CreateProductAction $action */
    $action = app(CreateProductAction::class);
    $product = $action->handle($attributes);

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->title)->toBe($attributes['title'])
        ->and($product->subtitle)->toBe($attributes['subtitle'])
        ->and($product->description)->toBe($attributes['description'])
        ->and($product->tags)->toBe($attributes['tags'])
        ->and($product->metadata)->toBe($attributes['metadata'])
        ->and($product->status)->toBe(Product::STATUS_DEFAULT);
});

it('creates a product and generates a handle from the title', function () {
    $attributes = [
        'title' => 'Test Product Handle',
    ];

    /** @var CreateProductAction $action */
    $action = app(CreateProductAction::class);
    $product = $action->handle($attributes);

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->handle)->toBe('test-product-handle');
});

it('creates a product with a custom status', function () {
    $attributes = [
        'title' => 'Test Product',
        'status' => 'active',
    ];

    /** @var CreateProductAction $action */
    $action = app(CreateProductAction::class);
    $product = $action->handle($attributes);

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->status)->toBe($attributes['status']);
});
