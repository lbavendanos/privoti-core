<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
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
