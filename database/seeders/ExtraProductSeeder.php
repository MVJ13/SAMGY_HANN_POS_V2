<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExtraProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('extra_products')->insert([
            // Alcohol
            ['name' => 'Soju',              'category' => 'Alcohol',    'price' => 150.00, 'active' => true, 'sort_order' => 1,  'created_at' => now(), 'updated_at' => now()],
            // Sodas
            ['name' => 'Coke',              'category' => 'Drinks',     'price' => 65.00,  'active' => true, 'sort_order' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sprite',            'category' => 'Drinks',     'price' => 65.00,  'active' => true, 'sort_order' => 11, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Royal',             'category' => 'Drinks',     'price' => 65.00,  'active' => true, 'sort_order' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pineapple Juice',   'category' => 'Drinks',     'price' => 65.00,  'active' => true, 'sort_order' => 13, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Soda 1.5L',         'category' => 'Drinks',     'price' => 109.00, 'active' => true, 'sort_order' => 14, 'created_at' => now(), 'updated_at' => now()],
            // Ice Cream
            ['name' => 'Melona',            'category' => 'Ice Cream',  'price' => 40.00,  'active' => true, 'sort_order' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pangpare Cone',     'category' => 'Ice Cream',  'price' => 50.00,  'active' => true, 'sort_order' => 21, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Greek Yogurt',      'category' => 'Ice Cream',  'price' => 70.00,  'active' => true, 'sort_order' => 22, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
