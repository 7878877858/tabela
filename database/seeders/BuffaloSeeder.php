<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BuffaloSeeder extends Seeder
{
    public function run(): void
    {
        $animals = [];

        // 3 Buffalo
        for ($i = 1; $i <= 5; $i++) {

            $animals[] = [
                'tag_number' => 'B' . (1000 + $i),
                'animal_type' => 'buffalo',
                'mother_buffalo_id' => null,
                'name' => fake()->firstName(),
                'gender' => fake()->randomElement(['male', 'female']),
                'weight' => rand(400, 700),
                'dob' => fake()->dateTimeBetween('-8 years', '-3 years')->format('Y-m-d'),
                'purchase_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                'purchase_price' => rand(100000, 180000),
                'status' => 'active',
                'lactation_status' => fake()->randomElement(['lactating', 'dry']),
                'notes' => fake()->city(),

                'heat_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
                'ai_date' => fake()->dateTimeBetween('-20 days', 'now')->format('Y-m-d'),
                'pregnancy_check_date' => fake()->dateTimeBetween('-10 days', 'now')->format('Y-m-d'),
                'expected_delivery_date' => fake()->dateTimeBetween('+3 months', '+8 months')->format('Y-m-d'),

                'birth_date' => null,
                'calf_tag_number' => null,
                'calf_gender' => null,
                'calf_weight' => null,

                'sold_date' => null,
                'sale_price' => null,
                'buyer_name' => null,
                'sold_reason' => null,

                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 3 Cow
        for ($i = 1; $i <= 5; $i++) {

            $animals[] = [
                'tag_number' => 'C' . (1000 + $i),
                'animal_type' => 'cow',
                'mother_buffalo_id' => null,
                'name' => fake()->firstName(),
                'gender' => fake()->randomElement(['male', 'female']),
                'weight' => rand(250, 550),
                'dob' => fake()->dateTimeBetween('-8 years', '-3 years')->format('Y-m-d'),
                'purchase_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                'purchase_price' => rand(70000, 150000),
                'status' => 'active',
                'lactation_status' => fake()->randomElement(['lactating', 'dry']),
                'notes' => fake()->city(),

                'heat_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
                'ai_date' => fake()->dateTimeBetween('-20 days', 'now')->format('Y-m-d'),
                'pregnancy_check_date' => fake()->dateTimeBetween('-10 days', 'now')->format('Y-m-d'),
                'expected_delivery_date' => fake()->dateTimeBetween('+3 months', '+8 months')->format('Y-m-d'),

                'birth_date' => null,
                'calf_tag_number' => null,
                'calf_gender' => null,
                'calf_weight' => null,

                'sold_date' => null,
                'sale_price' => null,
                'buyer_name' => null,
                'sold_reason' => null,

                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 2 Buffalo Calf
        for ($i = 1; $i <= 5; $i++) {

            $animals[] = [
                'tag_number' => 'BC' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'animal_type' => 'buffalo_calf',
                'mother_buffalo_id' => 1,

                'name' => 'Buffalo Calf ' . $i,
                'gender' => fake()->randomElement(['male', 'female']),
                'weight' => rand(15, 60),

                'dob' => null,
                'purchase_date' => null,
                'purchase_price' => null,

                'status' => 'active',
                'lactation_status' => 'dry',
                'notes' => null,

                'heat_date' => null,
                'ai_date' => null,
                'pregnancy_check_date' => null,
                'expected_delivery_date' => null,

                'birth_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),

                'calf_tag_number' => null,
                'calf_gender' => null,
                'calf_weight' => null,

                'sold_date' => null,
                'sale_price' => null,
                'buyer_name' => null,
                'sold_reason' => null,

                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 2 Cow Calf
        for ($i = 1; $i <= 5; $i++) {

            $animals[] = [
                'tag_number' => 'CC' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'animal_type' => 'cow_calf',
                'mother_buffalo_id' => 4,

                'name' => 'Cow Calf ' . $i,
                'gender' => fake()->randomElement(['male', 'female']),
                'weight' => rand(15, 60),

                'dob' => null,
                'purchase_date' => null,
                'purchase_price' => null,

                'status' => 'active',
                'lactation_status' => 'dry',
                'notes' => null,

                'heat_date' => null,
                'ai_date' => null,
                'pregnancy_check_date' => null,
                'expected_delivery_date' => null,

                'birth_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),

                'calf_tag_number' => null,
                'calf_gender' => null,
                'calf_weight' => null,

                'sold_date' => null,
                'sale_price' => null,
                'buyer_name' => null,
                'sold_reason' => null,

                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('buffaloes')->insert($animals);
    }
}