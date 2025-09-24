<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

it('returns the authenticated user', function () {
    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this->actingAs($user, 'cms')->getJson('/api/c/auth/user');

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.id', $user->id)
            ->where('data.email', $user->email)
            ->etc()
        );
});

it('updates the authenticated user', function () {
    $user = User::factory()->create();

    $attributes = [
        'name' => fake()->name(),
        'phone' => fake()->phoneNumber(),
        'dob' => fake()->date(),
    ];

    /** @var TestCase $this */
    $response = $this->actingAs($user, 'cms')->putJson('/api/c/auth/user', $attributes);

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.id', $user->id)
            ->where('data.name', $attributes['name'])
            ->where('data.phone', $attributes['phone'])
            ->where('data.dob', $attributes['dob'])
            ->etc()
        );
});

it('throws a validation error when updating the authenticated user with invalid attributes', function () {
    $user = User::factory()->create();

    $attributes = [
        'name' => '',
        'phone' => str_repeat('a', 256),
        'dob' => 'invalid-date',
    ];

    /** @var TestCase $this */
    $response = $this->actingAs($user, 'cms')->putJson('/api/c/auth/user', $attributes);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'phone', 'dob']);
});

it('authenticates a user', function () {
    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this->postJson('/c/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated('cms');

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.id', $user->id)
            ->where('data.email', $user->email)
            ->etc()
        );
});

it('fails to authenticate a user with invalid attributes', function () {
    $attributes = [
        'email' => 'invalid-email',
        'password' => '',
    ];

    /** @var TestCase $this */
    $response = $this->postJson('/c/login', $attributes);

    $this->assertGuest('cms');

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

it('fails to authenticate a user with invalid password', function () {
    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this->postJson('/c/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest('cms');

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('fails to authenticate a user with non-existing email', function () {
    /** @var TestCase $this */
    $response = $this->postJson('/c/login', [
        'email' => fake()->email(),
        'password' => 'password',
    ]);

    $this->assertGuest('cms');

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);

});

it('logs out an authenticated user', function () {
    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this->actingAs($user, 'cms')->postJson('/c/logout');

    $this->assertGuest('cms');

    $response->assertNoContent();
});
