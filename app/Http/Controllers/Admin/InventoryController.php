<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCondition;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display inventory overview
     */
    public function index()
    {
        $totalBooks = Book::sum('total_copies');
        $availableBooks = Book::sum('available_copies');
        $issuedBooks = $totalBooks - $availableBooks;
        $lowStockThreshold = 5; // Configurable threshold
        
        // Books with low stock (available copies <= threshold)
        $lowStockBooks = Book::where('available_copies', '<=', $lowStockThreshold)
            ->where('available_copies', '>', 0)
            ->with(['author', 'category', 'library'])
            ->orderBy('available_copies', 'asc')
            ->get();
        
        // Books with zero available copies
        $outOfStockBooks = Book::where('available_copies', 0)
            ->where('total_copies', '>', 0)
            ->with(['author', 'category', 'library'])
            ->get();
        
        // Missing books (reported as missing)
        $missingBooks = BookCondition::where('condition_type', 'missing')
            ->where('status', 'pending')
            ->with(['book.author', 'book.category', 'reportedBy'])
            ->get();
        
        // Damaged books
        $damagedBooks = BookCondition::where('condition_type', 'damaged')
            ->where('status', 'pending')
            ->with(['book.author', 'book.category', 'reportedBy'])
            ->get();
        
        // Books with missing copies (total > available + issued)
        $booksWithMissingCopies = Book::selectRaw('books.*, 
            (books.total_copies - books.available_copies - 
            (SELECT COUNT(*) FROM borrows WHERE borrows.book_id = books.id AND borrows.status = "borrowed")) as missing_copies')
            ->havingRaw('missing_copies > 0')
            ->with(['author', 'category', 'library'])
            ->get();
        
        $stats = [
            'total_books' => $totalBooks,
            'available_books' => $availableBooks,
            'issued_books' => $issuedBooks,
            'low_stock_count' => $lowStockBooks->count(),
            'out_of_stock_count' => $outOfStockBooks->count(),
            'missing_books_count' => $missingBooks->count(),
            'damaged_books_count' => $damagedBooks->count(),
            'books_with_missing_copies' => $booksWithMissingCopies->count(),
        ];
        
        return view('admin.inventory.index', compact(
            'stats',
            'lowStockBooks',
            'outOfStockBooks',
            'missingBooks',
            'damagedBooks',
            'booksWithMissingCopies',
            'lowStockThreshold'
        ));
    }
    
    /**
     * Display alerts page
     */
    public function alerts()
    {
        $lowStockThreshold = 5;
        
        $alerts = [
            'low_stock' => Book::where('available_copies', '<=', $lowStockThreshold)
                ->where('available_copies', '>', 0)
                ->with(['author', 'category', 'library'])
                ->orderBy('available_copies', 'asc')
                ->get(),
            
            'out_of_stock' => Book::where('available_copies', 0)
                ->where('total_copies', '>', 0)
                ->with(['author', 'category', 'library'])
                ->get(),
            
            'missing' => BookCondition::where('condition_type', 'missing')
                ->where('status', 'pending')
                ->with(['book.author', 'book.category', 'reportedBy'])
                ->get(),
            
            'damaged' => BookCondition::where('condition_type', 'damaged')
                ->where('status', 'pending')
                ->with(['book.author', 'book.category', 'reportedBy'])
                ->get(),
        ];
        
        return view('admin.inventory.alerts', compact('alerts', 'lowStockThreshold'));
    }
}
