<?php

declare(strict_types=1);

use App\Models\Collection;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

beforeEach(function () {
    $user = User::factory()->create();
    /** @var TestCase $this */
    $this->actingAs($user, 'cms');
});

it('returns a collection collection', function () {
    Collection::factory()->count(10)->create();

    /** @var TestCase $this */
    $response = $this->getJson('/api/c/collections');

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data', 10)
            ->has('meta')
        );
});
