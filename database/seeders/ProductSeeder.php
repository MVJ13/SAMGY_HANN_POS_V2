<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // ── Pork ─────────────────────────────────────────────────────────
            ['name' => 'Plain Pork',                  'category' => 'Meat',       'stock' => 70,    'unit' => 'kg',       'cost' => 250, 'reorder_level' => 15],
            ['name' => 'Garlic Pork',                 'category' => 'Meat',       'stock' => 78.4,  'unit' => 'kg',       'cost' => 270, 'reorder_level' => 15],
            ['name' => 'Bulgogi Pork',                'category' => 'Meat',       'stock' => 8,     'unit' => 'kg',       'cost' => 280, 'reorder_level' => 15],
            ['name' => 'Spicy Pork',                  'category' => 'Meat',       'stock' => 62,    'unit' => 'kg',       'cost' => 270, 'reorder_level' => 15],
            // ── Chicken ──────────────────────────────────────────────────────
            ['name' => 'Kaloohi BBQ',                 'category' => 'Meat',       'stock' => 60,    'unit' => 'kg',       'cost' => 300, 'reorder_level' => 15],
            ['name' => 'Buffalo Chicken',             'category' => 'Meat',       'stock' => 35,    'unit' => 'kg',       'cost' => 220, 'reorder_level' => 10],
            ['name' => 'Korean Shoyu Cheese Chicken', 'category' => 'Meat',       'stock' => 40,    'unit' => 'kg',       'cost' => 230, 'reorder_level' => 10],
            ['name' => 'Chicken Teriyaki',            'category' => 'Meat',       'stock' => 5,     'unit' => 'kg',       'cost' => 220, 'reorder_level' => 10],
            ['name' => 'Sour Cream Chicken',          'category' => 'Meat',       'stock' => 35,    'unit' => 'kg',       'cost' => 230, 'reorder_level' => 10],
            ['name' => 'Honey Mustard Chicken',       'category' => 'Meat',       'stock' => 35,    'unit' => 'kg',       'cost' => 230, 'reorder_level' => 10],
            ['name' => 'Cheesy Pesto Chicken',        'category' => 'Meat',       'stock' => 35,    'unit' => 'kg',       'cost' => 240, 'reorder_level' => 10],
            ['name' => 'Spicy Cheese Chicken',        'category' => 'Meat',       'stock' => 35,    'unit' => 'kg',       'cost' => 240, 'reorder_level' => 10],
            ['name' => 'Creamy Garlic Chicken',       'category' => 'Meat',       'stock' => 35,    'unit' => 'kg',       'cost' => 240, 'reorder_level' => 10],
            ['name' => 'Sriracha Chicken',            'category' => 'Meat',       'stock' => 7,     'unit' => 'kg',       'cost' => 230, 'reorder_level' => 10],
            // ── Beef & Other ─────────────────────────────────────────────────
            ['name' => 'Beef',                        'category' => 'Meat',       'stock' => 58.5,  'unit' => 'kg',       'cost' => 450, 'reorder_level' => 15],
            ['name' => 'Juicy',                       'category' => 'Meat',       'stock' => 25.8,  'unit' => 'kg',       'cost' => 200, 'reorder_level' => 10],
            ['name' => 'Gimbabsan',                   'category' => 'Meat',       'stock' => 50,    'unit' => 'kg',       'cost' => 180, 'reorder_level' => 10],
            // ── Grains ───────────────────────────────────────────────────────
            ['name' => 'Rice',                        'category' => 'Grains',     'stock' => 15,    'unit' => 'kg',       'cost' => 50,  'reorder_level' => 20],
            // ── Processed ────────────────────────────────────────────────────
            ['name' => 'Hot Dogs',                    'category' => 'Processed',  'stock' => 80,    'unit' => 'pcs',      'cost' => 5,   'reorder_level' => 30],
            ['name' => 'Pork Meatballs',              'category' => 'Processed',  'stock' => 100,   'unit' => 'pcs',      'cost' => 8,   'reorder_level' => 30],
            ['name' => 'Kikiam',                      'category' => 'Processed',  'stock' => 88,    'unit' => 'pcs',      'cost' => 6,   'reorder_level' => 30],
            // ── Vegetables ───────────────────────────────────────────────────
            ['name' => 'Tokwa/Tofu',                  'category' => 'Vegetables', 'stock' => 70,    'unit' => 'servings', 'cost' => 15,  'reorder_level' => 20],
            ['name' => 'Sweet Corn',                  'category' => 'Vegetables', 'stock' => 80,    'unit' => 'servings', 'cost' => 20,  'reorder_level' => 20],
            ['name' => 'Cucumber',                    'category' => 'Vegetables', 'stock' => 100,   'unit' => 'pcs',      'cost' => 15,  'reorder_level' => 20],
            ['name' => 'Lettuce',                     'category' => 'Vegetables', 'stock' => 115.6, 'unit' => 'heads',    'cost' => 30,  'reorder_level' => 25],
            ['name' => 'Garlic Bokcheon',             'category' => 'Vegetables', 'stock' => 50,    'unit' => 'servings', 'cost' => 35,  'reorder_level' => 15],
            // ── Drinks & Sauces ───────────────────────────────────────────────
            ['name' => 'Vinegar',                     'category' => 'Drinks',     'stock' => 50,    'unit' => 'bottles',  'cost' => 45,  'reorder_level' => 15],
            ['name' => 'Sparkling Water',             'category' => 'Drinks',     'stock' => 8,     'unit' => 'bottles',  'cost' => 60,  'reorder_level' => 15],
            ['name' => 'Gochujang',                   'category' => 'Sauce',      'stock' => 40,    'unit' => 'bottles',  'cost' => 150, 'reorder_level' => 10],
        ];

        foreach ($products as $data) {
            // updateOrCreate prevents duplicates if the seeder is run more than once
            Product::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
