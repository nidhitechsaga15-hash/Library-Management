<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Fiction', 'description' => 'Fictional literature'],
            ['name' => 'Non-Fiction', 'description' => 'Non-fictional works'],
            ['name' => 'Science', 'description' => 'Science and technology books'],
            ['name' => 'History', 'description' => 'Historical books'],
            ['name' => 'Biography', 'description' => 'Biographical works'],
            ['name' => 'Technology', 'description' => 'Technology and programming'],
            ['name' => 'Literature', 'description' => 'Classic and modern literature'],
            ['name' => 'Philosophy', 'description' => 'Philosophical works'],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }
    }
}
