<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'email' => 'abc@abc.com',
            'password' => Hash::make('abc123..'),
        ]);

        $categories = [
            'Men' => ['Shirts', 'T-Shirts', 'Pants', 'Jackets', 'Shoes'],
            'Women' => ['Dresses', 'Tops', 'Skirts', 'Pants', 'Jackets', 'Shoes'],
            'Kids' => [
                'Boys' => ['Shirts', 'Pants', 'Shoes'],
                'Girls' => ['Dresses', 'Skirts', 'Shoes'],
            ],
            'Accessories' => ['Bags', 'Hats', 'Scarves', 'Sunglasses']
        ];

        $this->createCategory($categories);

        ProductType::factory()
            ->count(7)
            ->sequence(
                ['name' => 'Casual'],
                ['name' => 'Formal'],
                ['name' => 'Sport'],
                ['name' => 'Business'],
                ['name' => 'Party'],
                ['name' => 'Outerwear'],
                ['name' => 'Footwear'],
            )
            ->create();
    }

    /**
     * Create categories recursively.
     *
     * @param array $categories
     * @param int|null $parent_id
     */
    function createCategory($categories, $parent_id = null)
    {
        foreach ($categories as $category => $subcategories) {
            if (is_array($subcategories)) {
                $parent = ProductCategory::factory()->create(['name' => $category, 'parent_id' => $parent_id]);

                $this->createCategory($subcategories, $parent->id);
                continue;
            }

            $parent = ProductCategory::factory()->create(['name' => $subcategories, 'parent_id' => $parent_id]);
        }
    }
}
