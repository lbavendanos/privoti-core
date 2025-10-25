<?php

declare(strict_types=1);

use App\Actions\Product\UpdateProductAction;
use App\Models\Collection;
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

it('updates a product and syncs its options', function () {
    $product = Product::factory()->create();

    $exsistingOption1 = $product->options()->create(['name' => 'Size']);
    $exsistingValues1 = $exsistingOption1->values()->createMany([
        ['value' => 'S'],
        ['value' => 'M'],
        ['value' => 'L'],
    ]);

    $exsistingOption2 = $product->options()->create(['name' => 'Color']);
    $exsistingValues2 = $exsistingOption2->values()->createMany([
        ['value' => 'Red'],
        ['value' => 'Blue'],
    ]);

    $attributes = [
        'options' => [
            [
                'id' => $exsistingOption1->id,
                'name' => 'Size',
                'values' => ['M', 'L', 'XL'],
            ],
            [
                'name' => 'Material',
                'values' => ['Cotton', 'Polyester'],
            ],
        ],
    ];

    /** @var UpdateProductAction $action */
    $action = app(UpdateProductAction::class);
    $updatedProduct = $action->handle($product, $attributes);
    /** @phpstan-ignore-next-line */
    $valuesOfSizeOption = $updatedProduct->options->firstWhere('name', 'Size')->values->pluck('value');
    /** @phpstan-ignore-next-line */
    $valuesOfMaterialOption = $updatedProduct->options->firstWhere('name', 'Material')->values->pluck('value');

    expect($updatedProduct)->toBeInstanceOf(Product::class)
        ->and($updatedProduct->options)->toHaveCount(2)
        ->and($updatedProduct->options->pluck('name'))->toContain('Size')
        ->and($updatedProduct->options->pluck('name'))->toContain('Material')
        ->and($valuesOfSizeOption)->toContain('M', 'L', 'XL')
        ->and($valuesOfSizeOption)->not->toContain('S')
        ->and($valuesOfMaterialOption)->toContain('Cotton', 'Polyester');
});

it('updates a product and syncs its variants', function () {
    $product = Product::factory()->create();

    $existingOption1 = $product->options()->create(['name' => 'Size']);
    $existingOption2 = $product->options()->create(['name' => 'Color']);

    $existingOption1->values()->createMany([
        ['value' => 'S'],
        ['value' => 'M'],
        ['value' => 'L'],
    ]);

    $existingOption2->values()->createMany([
        ['value' => 'Red'],
        ['value' => 'Blue'],
    ]);

    $existingVariant1 = $product->variants()->create([
        'name' => 'Variant 1',
        'price' => 19.99,
        'quantity' => 10,
    ]);

    $existingVariant2 = $product->variants()->create([
        'name' => 'Variant 2',
        'price' => 29.99,
        'quantity' => 5,
    ]);

    $attributes = [
        'variants' => [
            ['id' => $existingVariant1->id, 'name' => 'Updated Variant 1', 'price' => 17.99, 'quantity' => 15],
            ['name' => 'New Variant 3', 'price' => 39.99, 'quantity' => 8, 'options' => [
                ['value' => 'S'],
                ['value' => 'Red'],
            ]],
        ],
    ];

    /** @var UpdateProductAction $action */
    $action = app(UpdateProductAction::class);
    $updatedProduct = $action->handle($product, $attributes);
    $updatedVariants = $updatedProduct->variants;

    expect($updatedProduct)->toBeInstanceOf(Product::class)
        ->and($updatedVariants)->toBeInstanceOf(EloquentCollection::class)
        ->and($updatedVariants)->toHaveCount(2)
        ->and($updatedVariants->pluck('name'))->toContain('Updated Variant 1')
        ->and($updatedVariants->pluck('name'))->toContain('New Variant 3')
        ->and($updatedVariants->pluck('id'))->not->toContain($existingVariant2->id);
});

it('updates a product and syncs its collections', function () {
    $product = Product::factory()->create();

    /** @var EloquentCollection<int, Collection> $existingCollections */
    $existingCollections = Collection::factory()->count(2)->create();
    $product->collections()->attach($existingCollections->pluck('id')->toArray());

    /** @var EloquentCollection<int, Collection> $newCollections */
    $newCollections = Collection::factory()->count(2)->create();

    $attributes = [
        'collections' => $newCollections->pluck('id')->toArray(),
    ];

    /** @var UpdateProductAction $action */
    $action = app(UpdateProductAction::class);
    $updatedProduct = $action->handle($product, $attributes);
    $updatedCollectionIds = $updatedProduct->collections->pluck('id');

    expect($updatedProduct)->toBeInstanceOf(Product::class)
        ->and($updatedCollectionIds)->toHaveCount(2)
        /** @phpstan-ignore-next-line */
        ->and($updatedCollectionIds)->toContain($newCollections[0]->id, $newCollections[1]->id)
        /** @phpstan-ignore-next-line */
        ->and($updatedCollectionIds)->not->toContain($existingCollections[0]->id, $existingCollections[1]->id);
});

it('updates a product and detaches all collections when given an empty array', function () {
    $product = Product::factory()->create();

    /** @var EloquentCollection<int, Collection> $existingCollections */
    $existingCollections = Collection::factory()->count(2)->create();
    $product->collections()->attach($existingCollections->pluck('id')->toArray());

    $attributes = [
        'collections' => [],
    ];

    /** @var UpdateProductAction $action */
    $action = app(UpdateProductAction::class);
    $updatedProduct = $action->handle($product, $attributes);
    $updatedCollectionIds = $updatedProduct->collections->pluck('id');

    expect($updatedProduct)->toBeInstanceOf(Product::class)
        ->and($updatedCollectionIds)->toHaveCount(0);
});

it('updates a product and detaches all collections when given an null value', function () {
    $product = Product::factory()->create();

    /** @var EloquentCollection<int, Collection> $existingCollections */
    $existingCollections = Collection::factory()->count(2)->create();
    $product->collections()->attach($existingCollections->pluck('id')->toArray());

    $attributes = [
        'collections' => null,
    ];

    /** @var UpdateProductAction $action */
    $action = app(UpdateProductAction::class);
    $updatedProduct = $action->handle($product, $attributes);
    $updatedCollectionIds = $updatedProduct->collections->pluck('id');

    expect($updatedProduct)->toBeInstanceOf(Product::class)
        ->and($updatedCollectionIds)->toHaveCount(0);
});
