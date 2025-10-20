<?php

declare(strict_types=1);

use App\Actions\Product\CreateProductAction;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

it('creates a product with media', function () {
    Storage::fake('s3');

    $attributes = [
        'title' => 'Test Product with Media',
        'media' => [
            ['file' => UploadedFile::fake()->image('media1.jpg'), 'rank' => 1],
            ['file' => UploadedFile::fake()->image('media2.jpg'), 'rank' => 2],
        ],
    ];

    /** @var CreateProductAction $action */
    $action = app(CreateProductAction::class);
    $product = $action->handle($attributes);

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->media)->toHaveCount(2);
});
