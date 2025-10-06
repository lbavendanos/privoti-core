<?php

declare(strict_types=1);

use App\Actions\User\CreateUserAction;
use App\Models\User;

it('creates a user with basic attributes', function () {
    $attributes = [
        'name' => 'John Doe',
        'email' => 'm@example.com',
        'password' => 'password',
    ];

    $user = (new CreateUserAction())->handle($attributes);

    expect($user)
        ->toBeInstanceOf(User::class)
        ->and($user->name)->toBe($attributes['name'])
        ->and($user->email)->toBe($attributes['email'])
        ->and(password_verify('password', $user->password))->toBeTrue();
});
