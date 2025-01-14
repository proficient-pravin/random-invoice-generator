<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        
        // Seed 10 products with fake data
        foreach (range(1, 10) as $index) {
            Product::create([
                'product_name' => $faker->word(),
                'unit_price' => $faker->randomFloat(2, 10, 100), // Random price between 10 and 100
            ]);
        }
    }
}
