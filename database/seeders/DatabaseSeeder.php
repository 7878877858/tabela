<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        DB::table('users')->insert([
            'name'       => 'Admin',
            'email'      => 'admin@tabela.com',
            'password'   => Hash::make('admin123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Default settings
        $settings = [
            ['key' => 'farm_name',       'value' => 'મારો તબેલો'],
            ['key' => 'primary_color',   'value' => '#16a34a'],   // green
            ['key' => 'secondary_color', 'value' => '#15803d'],
            ['key' => 'milk_price',      'value' => '55'],         // default ₹/liter
            ['key' => 'currency',        'value' => '₹'],
        ];

        foreach ($settings as $s) {
            DB::table('settings')->insert(array_merge($s, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
        $this->call([
            BuffaloSeeder::class,
            MilkCustomerSeeder::class,
        ]);
    }
    
}