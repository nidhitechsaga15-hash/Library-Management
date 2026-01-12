<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\Author;
use App\Models\Category;
use Faker\Factory as Faker;

class FakeBooksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all authors and categories
        $authors = Author::pluck('id')->toArray();
        $categories = Category::pluck('id')->toArray();
        
        if (empty($authors) || empty($categories)) {
            if ($this->command) {
                $this->command->error('Please seed Authors and Categories first!');
            }
            return;
        }
        
        $bookTitles = [
            'Introduction to', 'Advanced', 'Fundamentals of', 'Complete Guide to', 
            'Mastering', 'Essential', 'Professional', 'Modern', 'Practical Guide to',
            'Comprehensive', 'The Art of', 'Understanding', 'Deep Dive into',
            'Exploring', 'Learning', 'Building', 'Creating', 'Designing', 'Developing'
        ];
        
        $bookSubjects = [
            'Computer Science', 'Mathematics', 'Physics', 'Chemistry', 'Biology',
            'Engineering', 'Programming', 'Data Structures', 'Algorithms', 'Database',
            'Web Development', 'Mobile Development', 'Machine Learning', 'Artificial Intelligence',
            'Networking', 'Security', 'Software Engineering', 'Operating Systems', 'Cloud Computing'
        ];
        
        $publishers = [
            'Tech Publications', 'Academic Press', 'Science Books', 'Engineering Publishers',
            'Digital Media', 'Knowledge Hub', 'Learning Press', 'Education Books', 'Professional Publishers'
        ];
        
        $count = 10000;
        if ($this->command && method_exists($this->command, 'argument')) {
            $count = (int)($this->command->argument('count') ?? 10000);
        }
        
        if ($this->command) {
            $this->command->info("Creating {$count} fake books...");
            $bar = $this->command->getOutput()->createProgressBar($count);
            $bar->start();
        }
        
        $books = [];
        for ($i = 1; $i <= $count; $i++) {
            $titlePrefix = $faker->randomElement($bookTitles);
            $subject = $faker->randomElement($bookSubjects);
            $title = $titlePrefix . ' ' . $subject . ' ' . $faker->numberBetween(1, 5);
            
            $totalCopies = $faker->numberBetween(1, 10);
            $availableCopies = $faker->numberBetween(0, $totalCopies);
            
            $books[] = [
                'isbn' => $faker->isbn13(),
                'title' => $title,
                'description' => $faker->paragraph(3),
                'author_id' => $faker->randomElement($authors),
                'category_id' => $faker->randomElement($categories),
                'publisher' => $faker->randomElement($publishers),
                'edition' => $faker->numberBetween(1, 5) . 'th Edition',
                'publication_year' => $faker->numberBetween(2010, 2024),
                'total_copies' => $totalCopies,
                'available_copies' => $availableCopies,
                'rack_number' => 'R' . $faker->numberBetween(1, 50) . '-' . $faker->numberBetween(1, 20),
                'language' => 'English',
                'pages' => $faker->numberBetween(200, 800),
                'status' => $availableCopies > 0 ? 'available' : 'unavailable',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Insert in batches of 500 for better performance
            if (count($books) >= 500) {
                Book::insert($books);
                $books = [];
            }
            
            if ($this->command) {
                $bar->advance();
            }
        }
        
        // Insert remaining books
        if (!empty($books)) {
            Book::insert($books);
        }
        
        if ($this->command) {
            $bar->finish();
            $this->command->newLine();
            $this->command->info("Successfully created {$count} fake books!");
        }
    }
}

