<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
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

it('updates the authenticated user password', function () {
    $user = User::factory()->create([
        'password' => 'old-password',
    ]);

    $attributes = [
        'current_password' => 'old-password',
        'password' => 'new-password',
    ];

    /** @var TestCase $this */
    $response = $this->actingAs($user, 'cms')->putJson('/api/c/auth/user/password', $attributes);

    $response->assertNoContent();
});

it('throws a validation error when updating the authenticated user password with invalid attributes', function () {
    $user = User::factory()->create([
        'password' => 'old-password',
    ]);

    $attributes = [
        'current_password' => 'wrong-password',
        'password' => 'short',
    ];

    /** @var TestCase $this */
    $response = $this->actingAs($user, 'cms')->putJson('/api/c/auth/user/password', $attributes);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['current_password', 'password']);
});

it('throws a validation error when updating the authenticated user password with missing attributes', function () {
    $user = User::factory()->create([
        'password' => 'old-password',
    ]);

    $attributes = [
        'current_password' => '',
        'password' => '',
    ];

    /** @var TestCase $this */
    $response = $this->actingAs($user, 'cms')->putJson('/api/c/auth/user/password', $attributes);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['current_password', 'password']);
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

it('requests a password reset link', function () {
    Notification::fake();

    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this->postJson('/c/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);

    $response
        ->assertOk();
});

it('fails to request a password reset link with invalid attributes', function () {
    $attributes = [
        'email' => 'invalid-email',
    ];

    /** @var TestCase $this */
    $response = $this->postJson('/c/forgot-password', $attributes);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('resets the user password with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    /** @var TestCase $this */
    $this->postJson('/c/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, function (ResetPassword $notification) use ($user) {
        /** @var TestCase $this */
        $response = $this->postJson('/c/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $this->assertAuthenticated('cms');

        $response
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('data.id', $user->id)
                ->where('data.email', $user->email)
                ->etc()
            );

        return true;
    });
});

it('fails to reset the user password with invalid attributes', function () {
    $attributes = [
        'token' => '',
        'email' => 'invalid-email',
        'password' => 'short',
    ];

    /** @var TestCase $this */
    $response = $this->postJson('/c/reset-password', $attributes);

    $this->assertGuest('cms');

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['token', 'email', 'password']);

});

it('fails to reset the user password with invalid token', function () {
    $user = User::factory()->create();

    $attributes = [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ];

    /** @var TestCase $this */
    $response = $this->postJson('/c/reset-password', $attributes);

    $this->assertGuest('cms');

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});
