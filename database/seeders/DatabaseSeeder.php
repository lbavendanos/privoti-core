<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
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

        ProductCategory::factory()
            ->count(3)
            ->sequence(
                ['name' => 'Shirts', 'handle' => 'shirts'],
                ['name' => 'Sweatshirts', 'handle' => 'sweatshirts'],
                ['name' => 'Pants', 'handle' => 'pants'],
            )
            ->create();
    }
}
