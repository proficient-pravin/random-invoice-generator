<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Seed 10 customers with fake data
        foreach (range(1, 10) as $index) {
            Customer::create([
                'email' => $faker->unique()->safeEmail(),
                'first_name' => $faker->firstName(),
                'last_name' => $faker->lastName(),
                'po_attention_to' => $faker->optional()->name(),
                'po_address_line1' => $faker->streetAddress(),
                'po_address_line2' => $faker->optional()->secondaryAddress(),
                'po_address_line3' => $faker->optional()->streetAddress(),
                'po_address_line4' => $faker->optional()->secondaryAddress(),
                'po_city' => $faker->city(),
                'po_region' => $faker->optional()->state(),
                'po_zip_code' => $faker->postcode(),
                'po_country' => $faker->country(),
                'sa_address_line1' => $faker->streetAddress(),
                'sa_address_line2' => $faker->optional()->secondaryAddress(),
                'sa_address_line3' => $faker->optional()->streetAddress(),
                'sa_address_line4' => $faker->optional()->secondaryAddress(),
                'sa_city' => $faker->city(),
                'sa_region' => $faker->optional()->state(),
                'sa_zip_code' => $faker->postcode(),
                'sa_country' => $faker->country(),
            ]);
        }
    }
}
