<?php

declare(strict_types=1);

use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

beforeEach(function () {
    $user = User::factory()->create();
    /** @var TestCase $this */
    $this->actingAs($user, 'cms');
});

it('returns a product category collection', function () {
    ProductCategory::factory()->count(10)->create();

    /** @var TestCase $this */
    $response = $this->getJson('/api/c/products/categories');

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data', 10)
            ->has('meta')
        );
});
