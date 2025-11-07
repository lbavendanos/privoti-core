<?php

declare(strict_types=1);

use Tests\TestCase;

it('registers a user via console command', function () {
    /** @var TestCase $this */
    /** @phpstan-ignore-next-line */
    $this->artisan('user:register')
        ->expectsQuestion('What is your name?', 'John Doe')
        ->expectsQuestion('What is your email?', 'm@exmaple.com')
        ->expectsQuestion('What is your password?', 'password')
        ->expectsQuestion('Please confirm your password', 'password')
        ->expectsOutput('User created successfully!')
        ->assertSuccessful();
});

it('fails to register a user with invalid data', function () {
    /** @var TestCase $this */
    /** @phpstan-ignore-next-line */
    $this->artisan('user:register')
        ->expectsQuestion('What is your name?', '')
        ->expectsQuestion('What is your email?', 'invalid-email')
        ->expectsQuestion('What is your password?', 'short')
        ->expectsQuestion('Please confirm your password', 'mismatch')
        ->expectsOutputToContain('The name field is required.')
        ->expectsOutputToContain('The email field must be a valid email address.')
        ->expectsOutputToContain('The password field confirmation does not match.')
        ->expectsOutputToContain('The password field must be at least 8 characters.')
        ->assertFailed();
});
