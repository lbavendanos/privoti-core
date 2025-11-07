<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\Cms\VerifyNewEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Stringable;
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
        'name' => 'John Doe',
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
        'email' => 'm@example.com',
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

it('sends an email verification notification to the authenticated user', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    /** @var TestCase $this */
    $response = $this->actingAs($user, 'cms')->postJson('/api/c/auth/user/email/notification');

    Notification::assertSentTo($user, VerifyEmail::class);

    $response->assertNoContent();
});

it('does not send an email verification notification if the authenticated user email is already verified', function () {
    Notification::fake();

    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this->actingAs($user, 'cms')->postJson('/api/c/auth/user/email/notification');

    Notification::assertNothingSent();

    $response->assertNoContent();
});

it('verifies the authenticated user email with valid hash', function () {
    Event::fake();

    $user = User::factory()->unverified()->create();

    /** @var TestCase $this */
    $response = $this->actingAs($user, 'cms')->getJson(
        URL::temporarySignedRoute(
            'auth.user.email.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        )
    );

    Event::assertDispatched(Verified::class);

    /** @phpstan-ignore-next-line */
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();

    $response->assertNoContent();
});

it('does not verify the authenticated user email with invalid hash', function () {
    $user = User::factory()->unverified()->create();

    /** @var TestCase $this */
    $response = $this->actingAs($user, 'cms')->getJson(
        URL::temporarySignedRoute(
            'auth.user.email.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        )
    );

    /** @phpstan-ignore-next-line */
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();

    $response->assertForbidden();
});

it('does not verify the authenticated user email if already verified', function () {
    Event::fake();

    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this->actingAs($user, 'cms')->getJson(
        URL::temporarySignedRoute(
            'auth.user.email.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        )
    );

    Event::assertNotDispatched(Verified::class);

    /** @phpstan-ignore-next-line */
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();

    $response->assertNoContent();
});

it('sends an email change verification notification to the new email address', function () {
    Notification::fake();

    $user = User::factory()->create();

    $attributes = [
        'email' => 'm@example.com',
    ];

    /** @var TestCase $this */
    $response = $this->actingAs($user, 'cms')->postJson('/api/c/auth/user/email/new/notification', $attributes);

    Notification::assertSentOnDemand(VerifyNewEmail::class,
        function (VerifyNewEmail $notification, $channels, AnonymousNotifiable $notifiable) use ($attributes) {
            /** @var Stringable $route */
            $route = $notifiable->routes['mail'];

            return $route->value() === $attributes['email'];
        }
    );

    $response->assertNoContent();
});

it('fails to send an email change verification notification with invalid attributes', function () {
    Notification::fake();

    $user = User::factory()->create();

    $attributes = [
        'email' => 'invalid-email',
    ];

    /** @var TestCase $this */
    $response = $this->actingAs($user, 'cms')->postJson('/api/c/auth/user/email/new/notification', $attributes);

    Notification::assertNothingSent();

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('verifies the new email address with valid hash', function () {
    Event::fake();

    $user = User::factory()->create();
    $newEmail = 'm@example.com';

    /** @var TestCase $this */
    $response = $this->actingAs($user, 'cms')->getJson(
        URL::temporarySignedRoute(
            'auth.user.email.new.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'email' => $newEmail, 'hash' => sha1($newEmail)]
        )
    );

    Event::assertDispatched(Verified::class);

    /** @phpstan-ignore-next-line */
    expect($user->fresh()->email)->toBe($newEmail);
    /** @phpstan-ignore-next-line */
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();

    $response->assertNoContent();
});

it('does not verify the new email address with invalid hash', function () {
    Event::fake();

    $user = User::factory()->create();
    $newEmail = 'm@example.com';

    /** @var TestCase $this */
    $response = $this->actingAs($user, 'cms')->getJson(
        URL::temporarySignedRoute(
            'auth.user.email.new.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'email' => $newEmail, 'hash' => sha1('wrong-email')]
        )
    );

    Event::assertNotDispatched(Verified::class);

    /** @phpstan-ignore-next-line */
    expect($user->fresh()->email)->not->toBe($newEmail);

    $response->assertForbidden();
});

it('does not verify the new email address if the id does not match the authenticated user', function () {
    Event::fake();

    $user = User::factory()->create();
    $newEmail = 'm@example.com';

    /** @var TestCase $this */
    $response = $this->actingAs($user, 'cms')->getJson(
        URL::temporarySignedRoute(
            'auth.user.email.new.verify',
            now()->addMinutes(60),
            ['id' => $user->id + 1, 'email' => $newEmail, 'hash' => sha1($newEmail)]
        )
    );

    Event::assertNotDispatched(Verified::class);

    /** @phpstan-ignore-next-line */
    expect($user->fresh()->email)->not->toBe($newEmail);

    $response->assertForbidden();
});

it('does not verify the new email address if the new email is the same as the current email', function () {
    Event::fake();

    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this->actingAs($user, 'cms')->getJson(
        URL::temporarySignedRoute(
            'auth.user.email.new.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'email' => $user->email, 'hash' => sha1($user->email)]
        )
    );

    Event::assertNotDispatched(Verified::class);

    /** @phpstan-ignore-next-line */
    expect($user->fresh()->email)->toBe($user->email);

    $response->assertNoContent();
});
