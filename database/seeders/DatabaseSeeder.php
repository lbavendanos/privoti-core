<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'email' => 'abc@abc.com',
        ]);

        Collection::factory()
            ->count(5)
            ->sequence(
                ['title' => 'Summer Collection', 'handle' => 'summer-collection'],
                ['title' => 'Winter Collection', 'handle' => 'winter-collection'],
                ['title' => 'Spring Collection', 'handle' => 'spring-collection'],
                ['title' => 'Autumn Collection', 'handle' => 'autumn-collection'],
                ['title' => 'Fall Collection', 'handle' => 'fall-collection'],
            )
            ->create();

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

        Vendor::factory()
            ->count(5)
            ->sequence(
                ['name' => 'Nike'],
                ['name' => 'Adidas'],
                ['name' => 'Puma'],
                ['name' => 'Reebok'],
                ['name' => 'Under Armour'],
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
                $parent = ProductCategory::factory()->create([
                    'name' => $category,
                    'handle' => Str::slug($category),
                    'parent_id' => $parent_id
                ]);

                $this->createCategory($subcategories, $parent->id);
                continue;
            }

            $parent = ProductCategory::factory()->create([
                'name' => $subcategories,
                'handle' => Str::slug($subcategories),
                'parent_id' => $parent_id
            ]);
        }
    }
}
