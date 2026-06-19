<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MilkCustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [];

        for ($i = 1; $i <= 5; $i++) {

            $customers[] = [
                'name' => fake()->name(),
                'mobile' => fake()->numerify('9#########'),
                'address' => fake()->address(),
                'status' => fake()->randomElement(['active', 'inactive']),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('milk_customers')->insert($customers);
    }
}