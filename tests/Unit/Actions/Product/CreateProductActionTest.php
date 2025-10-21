<?php

declare(strict_types=1);

use App\Actions\Product\CreateProductAction;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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
        ->and($product->status)->toBe(Product::STATUS_DEFAULT)
        ->and($product->tags)->toBe($attributes['tags'])
        ->and($product->metadata)->toBe($attributes['metadata']);
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

it('creates a product with relations to category, type and vendor', function () {
    $category = ProductCategory::factory()->create();
    $type = ProductType::factory()->create();
    $vendor = Vendor::factory()->create();

    $attributes = [
        'title' => 'Test Product',
        'category_id' => $category->id,
        'type_id' => $type->id,
        'vendor_id' => $vendor->id,
    ];

    /** @var CreateProductAction $action */
    $action = app(CreateProductAction::class);
    $product = $action->handle($attributes);

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->category_id)->toBe($attributes['category_id'])
        ->and($product->type_id)->toBe($attributes['type_id'])
        ->and($product->vendor_id)->toBe($attributes['vendor_id']);
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

it('creates a product with options', function () {
    $attributes = [
        'title' => 'Test Product with Options',
        'options' => [
            [
                'name' => 'Size',
                'values' => ['Small', 'Medium', 'Large'],
            ],
            [
                'name' => 'Color',
                'values' => ['Red', 'Blue', 'Green'],
            ],
        ],
    ];

    /** @var CreateProductAction $action */
    $action = app(CreateProductAction::class);
    $product = $action->handle($attributes);

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->options)->toHaveCount(2);
});

it('creates a product with variants', function () {
    $attributes = [
        'title' => 'Test Product with Variants',
        'options' => [
            [
                'name' => 'Size',
                'values' => ['Small', 'Medium', 'Large'],
            ],
            [
                'name' => 'Color',
                'values' => ['Red', 'Blue', 'Green'],
            ],
        ],
        'variants' => [
            [
                'name' => 'Variant 1',
                'price' => 19.99,
                'quantity' => 10,
                'options' => [
                    ['value' => 'Small'],
                    ['value' => 'Red'],
                ],
            ],
            [
                'name' => 'Variant 2',
                'price' => 29.99,
                'quantity' => 5,
                'options' => [
                    ['value' => 'Large'],
                    ['value' => 'Blue'],
                ],
            ],
        ],
    ];

    /** @var CreateProductAction $action */
    $action = app(CreateProductAction::class);
    $product = $action->handle($attributes);

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->variants)->toHaveCount(2);
});

it('creates a product and attaches it to collections', function () {
    /** @var EloquentCollection<int, Collection> $collections */
    $collections = Collection::factory()->count(2)->create();

    $attributes = [
        'title' => 'Test Product with Collections',
        'collections' => $collections->pluck('id')->toArray(),
    ];

    /** @var CreateProductAction $action */
    $action = app(CreateProductAction::class);
    $product = $action->handle($attributes);

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->collections)->toHaveCount(2);
});
