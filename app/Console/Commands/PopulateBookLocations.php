<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Book;
use Illuminate\Support\Facades\DB;

class PopulateBookLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:populate-locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate random location data (almirah, row, book_serial) for existing books that don\'t have location data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to populate book locations...');

        // Get books without location data
        $books = Book::where(function($query) {
            $query->whereNull('almirah')
                  ->orWhereNull('row')
                  ->orWhereNull('book_serial');
        })->get();

        if ($books->isEmpty()) {
            $this->info('No books found without location data.');
            return Command::SUCCESS;
        }

        $this->info("Found {$books->count()} books without location data.");

        $updated = 0;
        $almirahLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        
        foreach ($books as $book) {
            try {
                $updateData = [];
                
                // Generate random almirah if not set
                if (!$book->almirah) {
                    $almirahLetter = $almirahLetters[array_rand($almirahLetters)];
                    $almirahNumber = rand(1, 10);
                    $updateData['almirah'] = $almirahLetter . '-' . $almirahNumber;
                }
                
                // Generate random row if not set
                if (!$book->row) {
                    $rowNumber = rand(1, 20);
                    $updateData['row'] = 'R-' . str_pad($rowNumber, 2, '0', STR_PAD_LEFT);
                }
                
                // Generate random serial if not set
                if (!$book->book_serial) {
                    $serialNumber = rand(1, 999);
                    $updateData['book_serial'] = 'S-' . str_pad($serialNumber, 3, '0', STR_PAD_LEFT);
                }
                
                if (!empty($updateData)) {
                    $book->update($updateData);
                    $updated++;
                    $almirah = $updateData['almirah'] ?? $book->almirah;
                    $row = $updateData['row'] ?? $book->row;
                    $serial = $updateData['book_serial'] ?? $book->book_serial;
                    $this->line("Updated: {$book->title} - Almirah: {$almirah}, Row: {$row}, Serial: {$serial}");
                }
            } catch (\Exception $e) {
                $this->error("Error updating book #{$book->id}: " . $e->getMessage());
            }
        }

        $this->info("Completed! Updated {$updated} books with location data.");
        return Command::SUCCESS;
    }
}
