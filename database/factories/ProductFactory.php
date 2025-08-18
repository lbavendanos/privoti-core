<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
final class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'subtitle' => fake()->sentence(),
            'handle' => fake()->slug(),
            'description' => fake()->paragraph(1),
            'status' => fake()->randomElement(Product::STATUS_LIST),
            'tags' => fake()->words(3),
        ];
    }
}
