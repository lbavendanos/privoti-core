<?php

declare(strict_types=1);

use App\Actions\User\RegisterUserAction;
use App\Models\User;

it('registers a user and verifies email', function () {
    $attributes = [
        'name' => 'John Doe',
        'email' => 'm@example.com',
        'password' => 'password',
    ];

    $user = app(RegisterUserAction::class)->handle($attributes);

    expect($user)
        ->toBeInstanceOf(User::class)
        ->and($user->name)->toBe($attributes['name'])
        ->and($user->email)->toBe($attributes['email'])
        ->and($user->email_verified_at)->not->toBeNull()
        ->and(password_verify('password', $user->password))->toBeTrue();
});
