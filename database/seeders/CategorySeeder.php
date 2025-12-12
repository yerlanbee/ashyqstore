<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'id' => 1,
                'name' => 'Горячее блюдо',
                'slug' => 'goryachee-blyudo',
                'sort' => 0,
                'is_visible' => true,
                'image' => null,
            ]
        ];

        DB::table('categories')->insert($categories);
    }
}
