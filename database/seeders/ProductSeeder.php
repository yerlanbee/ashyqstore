<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'id' => 1,
                'category_id' => 1,
                'name' => 'Плов',
                'slug' => 'plov',
                'quantity' => 5,
                'price' => 1000,
                'sort' => 0,
                'image' => 'plov.png',
                'is_visible' => true,
            ],
            [
                'id' => 2,
                'category_id' => 1,
                'name' => 'Лагман',
                'slug' => 'lagman',
                'quantity' => 3,
                'price' => 1300,
                'sort' => 0,
                'image' => 'lagman.png',
                'is_visible' => true,
            ],
        ];


        DB::table('products')->insert($products);
    }
}
