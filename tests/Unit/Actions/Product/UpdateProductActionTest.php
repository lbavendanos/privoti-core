<?php

declare(strict_types=1);

use App\Actions\Product\UpdateProductAction;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductMedia;
use App\Models\ProductType;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('updates a product with basic attributes', function () {
    $product = Product::factory()->create();

    $attributes = [
        'title' => 'Updated Test Product',
        'subtitle' => 'This is an updated test product',
        'description' => 'Detailed description of the updated test product.',
        'tags' => ['updated', 'test', 'product'],
        'metadata' => ['color' => 'blue', 'size' => 'L'],
    ];

    /** @var UpdateProductAction $action */
    $action = app(UpdateProductAction::class);
    $updatedProduct = $action->handle($product, $attributes);

    expect($updatedProduct)->toBeInstanceOf(Product::class)
        ->and($updatedProduct->title)->toBe($attributes['title'])
        ->and($updatedProduct->subtitle)->toBe($attributes['subtitle'])
        ->and($updatedProduct->description)->toBe($attributes['description'])
        ->and($updatedProduct->tags)->toBe($attributes['tags'])
        ->and($updatedProduct->metadata)->toBe($attributes['metadata']);
});

it('updates a product and generates a new handle from the title', function () {
    $product = Product::factory()->create([
        'title' => 'Original Product Title',
        'handle' => 'original-product-title',
    ]);

    $attributes = [
        'title' => 'New Product Title',
    ];

    /** @var UpdateProductAction $action */
    $action = app(UpdateProductAction::class);
    $updatedProduct = $action->handle($product, $attributes);

    expect($updatedProduct)->toBeInstanceOf(Product::class)
        ->and($updatedProduct->handle)->toBe('new-product-title');
});

it('updates a product and changes its status', function () {
    $product = Product::factory()->create([
        'status' => 'draft',
    ]);

    $attributes = [
        'status' => 'active',
    ];

    /** @var UpdateProductAction $action */
    $action = app(UpdateProductAction::class);
    $updatedProduct = $action->handle($product, $attributes);

    expect($updatedProduct)->toBeInstanceOf(Product::class)
        ->and($updatedProduct->status)->toBe('active');
});

it('updates a product with new relations to category, type and vendor', function () {
    $product = Product::factory()->create();

    $newCategory = ProductCategory::factory()->create();
    $newType = ProductType::factory()->create();
    $newVendor = Vendor::factory()->create();

    $attributes = [
        'category_id' => $newCategory->id,
        'type_id' => $newType->id,
        'vendor_id' => $newVendor->id,
    ];

    /** @var UpdateProductAction $action */
    $action = app(UpdateProductAction::class);
    $updatedProduct = $action->handle($product, $attributes);

    expect($updatedProduct)->toBeInstanceOf(Product::class)
        ->and($updatedProduct->category_id)->toBe($newCategory->id)
        ->and($updatedProduct->type_id)->toBe($newType->id)
        ->and($updatedProduct->vendor_id)->toBe($newVendor->id);
});

it('updates a product and syncs its media', function () {
    Storage::fake('s3');

    $product = Product::factory()->create();
    $existingMedia1 = ProductMedia::factory()->for($product)->create(['rank' => 1]);
    $existingMedia2 = ProductMedia::factory()->for($product)->create(['rank' => 2]);

    $attributes = [
        'media' => [
            ['id' => $existingMedia1->id, 'rank' => 3],
            ['file' => UploadedFile::fake()->image('new_media.jpg'), 'rank' => 4],
        ],
    ];

    /** @var UpdateProductAction $action */
    $action = app(UpdateProductAction::class);
    $updatedProduct = $action->handle($product, $attributes);
    $updatedMedia = $updatedProduct->media;

    expect($updatedProduct)->toBeInstanceOf(Product::class)
        ->and($updatedMedia)->toBeInstanceOf(EloquentCollection::class)
        ->and($updatedMedia)->toHaveCount(2)
        ->and($updatedMedia->pluck('id'))->toContain($existingMedia1->id)
        ->and($updatedMedia->pluck('rank'))->toContain(3)
        ->and($updatedMedia->pluck('rank'))->toContain(4)
        ->and($updatedMedia->pluck('id'))->not->toContain($existingMedia2->id);
});
