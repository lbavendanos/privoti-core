<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
final class CustomerFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    private static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'dob' => fake()->date(),
        ];
    }

    /**
     * Indicate that the customer is a guest.
     */
    public function guest(): static
    {
        return $this->state(fn (array $attributes): array => [
            'account' => 'guest',
            'email_verified_at' => null,
            'password' => null,
            'remember_token' => null,
        ]);
    }

    /**
     * Indicate that the customer is a registered user.
     */
    public function registered(): static
    {
        return $this->state(fn (array $attributes): array => [
            'account' => 'registered',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);
    }
}
