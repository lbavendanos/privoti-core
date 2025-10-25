<?php

declare(strict_types=1);

use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

beforeEach(function () {
    $user = User::factory()->create();
    /** @var TestCase $this */
    $this->actingAs($user, 'cms');
});

it('returns a product collection', function () {
    Product::factory()->count(10)->create();

    /** @var TestCase $this */
    $response = $this->getJson('/api/c/products');

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data', 10)
            ->has('meta')
        );
});

it('creates a product', function () {
    Storage::fake('s3');

    $category = ProductCategory::factory()->create();
    $type = ProductType::factory()->create();
    $vendor = Vendor::factory()->create();
    $collection = Collection::factory()->count(3)->create();

    $attributes = [
        'title' => 'Test Product',
        'subtitle' => 'A great product',
        'description' => 'This is a detailed description of the test product.',
        'status' => 'active',
        'tags' => ['tag1', 'tag2'],
        'category_id' => $category->id,
        'type_id' => $type->id,
        'vendor_id' => $vendor->id,
        'collections' => $collection->pluck('id')->toArray(),
        'media' => [
            [
                'file' => UploadedFile::fake()->image('product1.jpg'),
                'rank' => 1,
            ],
            [
                'file' => UploadedFile::fake()->image('product2.jpg'),
                'rank' => 2,
            ],
        ],
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
                'name' => 'Small Red',
                'price' => 19.99,
                'quantity' => 100,
                'options' => [
                    ['value' => 'Small'],
                    ['value' => 'Red'],
                ],
            ],
            [
                'name' => 'Medium Blue',
                'price' => 21.99,
                'quantity' => 150,
                'options' => [
                    ['value' => 'Medium'],
                    ['value' => 'Blue'],
                ],
            ],
        ],
    ];

    /** @var TestCase $this */
    $response = $this->postJson('/api/c/products', $attributes);

    $response
        ->assertCreated()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.title', $attributes['title'])
            ->where('data.handle', Str::slug($attributes['title']))
            ->where('data.subtitle', $attributes['subtitle'])
            ->where('data.description', $attributes['description'])
            ->where('data.status', $attributes['status'])
            ->where('data.category_id', $category->id)
            ->where('data.type_id', $type->id)
            ->where('data.vendor_id', $vendor->id)
            ->has('data.media', 2)
            ->has('data.options', 2)
            ->has('data.variants', 2)
            ->has('data.collections', 3)
            ->etc()
        );

});

it('throws a validation error when creating a product with invalid attributes', function () {
    $attributes = [
        'title' => '',
        'subtitle' => str_repeat('a', 300),
        'description' => '',
        'status' => 'invalid_status',
        'tags' => 'not_an_array',
        'category_id' => 9999,
        'type_id' => 9999,
        'vendor_id' => 9999,
        'collections' => 'not_an_array',
        'media' => [
            [
                'file' => 'not_a_file',
                'rank' => 'not_an_integer',
            ],
        ],
        'options' => [
            [
                'name' => '',
                'values' => 'not_an_array',
            ],
        ],
        'variants' => [
            [
                'name' => '',
                'price' => 'not_a_number',
                'quantity' => 'not_an_integer',
                'options' => 'not_an_array',
            ],
        ],
    ];

    /** @var TestCase $this */
    $response = $this->postJson('/api/c/products', $attributes);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'title',
            'subtitle',
            'status',
            'tags',
            'category_id',
            'type_id',
            'vendor_id',
            'collections',
            'media.0.file',
            'media.0.rank',
            'options.0.name',
            'options.0.values',
            'variants.0.name',
            'variants.0.price',
            'variants.0.quantity',
            'variants.0.options',
        ]);
});

it('show a product', function () {
    $product = Product::factory()->create();

    /** @var TestCase $this */
    $response = $this->getJson(sprintf('/api/c/products/%s', $product->id));

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.id', $product->id)
            ->where('data.title', $product->title)
            ->where('data.subtitle', $product->subtitle)
            ->where('data.handle', $product->handle)
            ->etc()
        );
});

it('updates a product', function () {
    Storage::fake('s3');

    $product = Product::factory()->create();

    $category = ProductCategory::factory()->create();
    $type = ProductType::factory()->create();
    $vendor = Vendor::factory()->create();
    $collection = Collection::factory()->count(2)->create();

    $attributes = [
        'title' => 'Updated Product Title',
        'subtitle' => 'Updated Subtitle',
        'description' => 'Updated description of the product.',
        'status' => 'active',
        'tags' => ['updated_tag1', 'updated_tag2'],
        'category_id' => $category->id,
        'type_id' => $type->id,
        'vendor_id' => $vendor->id,
        'collections' => $collection->pluck('id')->toArray(),
        'media' => [
            [
                'file' => UploadedFile::fake()->image('updated_product1.jpg'),
                'rank' => 1,
            ],
            [
                'file' => UploadedFile::fake()->image('updated_product2.jpg'),
                'rank' => 2,
            ],
        ],
        'options' => [
            [
                'name' => 'Material',
                'values' => ['Cotton', 'Polyester'],
            ],
            [
                'name' => 'Size',
                'values' => ['Small', 'Medium', 'Large'],
            ],
        ],
        'variants' => [
            [
                'name' => 'Small Cotton Variant',
                'price' => 25.99,
                'quantity' => 200,
                'options' => [
                    ['value' => 'Small'],
                    ['value' => 'Cotton'],
                ],
            ],
            [
                'name' => 'Medium Polyester Variant',
                'price' => 27.99,
                'quantity' => 250,
                'options' => [
                    ['value' => 'Medium'],
                    ['value' => 'Polyester'],
                ],
            ],
        ],
    ];

    /** @var TestCase $this */
    $response = $this->putJson("/api/c/products/{$product->id}", $attributes);

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.id', $product->id)
            ->where('data.title', $attributes['title'])
            ->where('data.subtitle', $attributes['subtitle'])
            ->where('data.description', $attributes['description'])
            ->where('data.status', $attributes['status'])
            ->where('data.category_id', $category->id)
            ->where('data.type_id', $type->id)
            ->where('data.vendor_id', $vendor->id)
            ->has('data.media', 2)
            ->has('data.options', 2)
            ->has('data.variants', 2)
            ->has('data.collections', 2)
            ->etc()
        );
});

it('throws a validation error when updating a product with invalid attributes', function () {
    $product = Product::factory()->create();

    $attributes = [
        'title' => '',
        'subtitle' => str_repeat('a', 300),
        'description' => '',
        'status' => 'invalid_status',
        'tags' => 'not_an_array',
        'category_id' => 9999,
        'type_id' => 9999,
        'vendor_id' => 9999,
        'collections' => 'not_an_array',
        'media' => [
            [
                'file' => 'not_a_file',
                'rank' => 'not_an_integer',
            ],
        ],
        'options' => [
            [
                'name' => '',
                'values' => 'not_an_array',
            ],
        ],
        'variants' => [
            [
                'name' => '',
                'price' => 'not_a_number',
                'quantity' => 'not_an_integer',
                'options' => 'not_an_array',
            ],
        ],
    ];

    /** @var TestCase $this */
    $response = $this->putJson("/api/c/products/{$product->id}", $attributes);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'title',
            'subtitle',
            'status',
            'tags',
            'category_id',
            'type_id',
            'vendor_id',
            'collections',
            'media.0.file',
            'media.0.rank',
            'options.0.name',
            'options.0.values',
            'variants.0.name',
            'variants.0.price',
            'variants.0.quantity',
            'variants.0.options',
        ]);
});
