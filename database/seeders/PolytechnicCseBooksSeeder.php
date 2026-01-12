<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;

class PolytechnicCseBooksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $course = 'Computer Science & Engineering Polytechnic';
        $semester = '1st Sem';

        $author = Author::firstOrCreate(
            ['name' => 'Board of Technical Education'],
            ['bio' => 'Default syllabus author for Polytechnic CSE', 'nationality' => 'IN']
        );

        $category = Category::firstOrCreate(
            ['name' => 'Polytechnic CSE 1st Year'],
            ['description' => 'Polytechnic CSE 1st year syllabus books']
        );

        $books = [
            ['title' => 'Basic Mathematics – I', 'subject' => 'Mathematics'],
            ['title' => 'Applied Physics – I', 'subject' => 'Physics'],
            ['title' => 'Applied Chemistry – I', 'subject' => 'Chemistry'],
            ['title' => 'Computer Fundamentals & IT / ICT', 'subject' => 'Computer Fundamentals'],
            ['title' => 'Communication Skills / English – I', 'subject' => 'English'],
            ['title' => 'Engineering Graphics – I', 'subject' => 'Engineering Graphics'],
            ['title' => 'Workshop Practice – I', 'subject' => 'Workshop Practice'],
            ['title' => 'Environmental Science (Optional)', 'subject' => 'Environmental Science'],
            ['title' => 'Values & Ethics (Optional)', 'subject' => 'Values & Ethics'],
            ['title' => 'Labs (Physics + Chemistry + Computer)', 'subject' => 'Labs'],
        ];

        foreach ($books as $index => $data) {
            $total = random_int(3, 5);
            $isbn = 'POLY-CSE-1S-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);

            Book::updateOrCreate(
                ['isbn' => $isbn],
                [
                    'title' => $data['title'],
                    'description' => $data['title'] . ' syllabus book for CSE Polytechnic 1st Sem.',
                    'author_id' => $author->id,
                    'category_id' => $category->id,
                    'publisher' => 'Polytechnic Press',
                    'publication_year' => now()->year,
                    'total_copies' => $total,
                    'available_copies' => $total,
                    'language' => 'English',
                    'pages' => 180,
                    'status' => 'available',
                    'course' => $course,
                    'semester' => $semester,
                    'subject' => $data['subject'],
                ]
            );
        }
    }
}











