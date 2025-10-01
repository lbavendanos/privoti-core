<?php

declare(strict_types=1);

use App\Actions\User\UpdateUserAction;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    /** @phpstan-ignore-next-line */
    $this->user = User::factory()->create([
        'password' => 'password',
    ]);
});

it('updates a user by model instance', function () {
    $attributes = [
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => 'newpassword',
    ];

    /** @var TestCase $this */
    /** @phpstan-ignore-next-line */
    $updated = (new UpdateUserAction())->handle($this->user, $attributes);

    expect($updated)->toBeInstanceOf(User::class)
        ->and($updated->name)->toBe($attributes['name'])
        ->and($updated->email)->toBe($attributes['email'])
        ->and(password_verify($attributes['password'], $updated->password))->toBeTrue();
});

it('updates a user by id', function () {
    $attributes = [
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => 'newpassword',
    ];

    /** @var TestCase $this */
    /** @phpstan-ignore-next-line */
    $updated = (new UpdateUserAction())->handle($this->user->id, $attributes);

    expect($updated)->toBeInstanceOf(User::class)
        ->and($updated->name)->toBe($attributes['name'])
        ->and($updated->email)->toBe($attributes['email'])
        ->and(password_verify($attributes['password'], $updated->password))->toBeTrue();
});
