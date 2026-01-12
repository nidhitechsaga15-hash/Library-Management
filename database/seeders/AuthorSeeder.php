<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authors = [
            ['name' => 'J.K. Rowling', 'nationality' => 'British'],
            ['name' => 'George R.R. Martin', 'nationality' => 'American'],
            ['name' => 'Stephen King', 'nationality' => 'American'],
            ['name' => 'Agatha Christie', 'nationality' => 'British'],
            ['name' => 'William Shakespeare', 'nationality' => 'British'],
            ['name' => 'Jane Austen', 'nationality' => 'British'],
            ['name' => 'Charles Dickens', 'nationality' => 'British'],
            ['name' => 'Mark Twain', 'nationality' => 'American'],
        ];

        foreach ($authors as $author) {
            \App\Models\Author::create($author);
        }
    }
}
