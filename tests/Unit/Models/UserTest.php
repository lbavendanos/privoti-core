<?php

declare(strict_types=1);

use App\Models\User;

it('can create a user', function () {
    $user = User::factory()->create();

    expect($user)->toBeInstanceOf(User::class);
    expect($user->id)->toBeGreaterThan(0);
});

it('can format name to uppercase first letter', function () {
    $user = User::factory()->create([
        'name' => 'john doe',
    ]);

    expect($user->name)->toBe('John Doe');
});
