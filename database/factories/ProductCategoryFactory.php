<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'handle' => fake()->slug(),
            'description' => fake()->sentence(),
            'is_active' => true,
            'is_public' => true,
            'rank' => 0,
        ];
    }
}
