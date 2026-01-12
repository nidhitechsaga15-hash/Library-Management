<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScannerController extends Controller
{
    public function index()
    {
        return view('staff.scanner.index');
    }

    public function scan(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = $request->code;
        
        // Try to find book by ISBN or ID
        $book = Book::where('isbn', $code)
            ->orWhere('id', $code)
            ->first();

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found!',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'isbn' => $book->isbn,
                'available_copies' => $book->available_copies,
                'status' => $book->status,
            ],
        ]);
    }

    public function issueByScan(Request $request)
    {
        $request->validate([
            'book_code' => 'required|string',
            'user_code' => 'required|string',
        ]);

        // Find book
        $book = Book::where('isbn', $request->book_code)
            ->orWhere('id', $request->book_code)
            ->first();

        if (!$book) {
            return redirect()->back()->with('error', 'Book not found!');
        }

        // Find user by student_id, email, or ID
        $user = User::where('student_id', $request->user_code)
            ->orWhere('email', $request->user_code)
            ->orWhere('id', $request->user_code)
            ->where('role', 'student')
            ->first();

        if (!$user) {
            return redirect()->back()->with('error', 'Student not found!');
        }

        if (!$book->isAvailable()) {
            return redirect()->back()->with('error', 'Book is not available!');
        }

        // Check book limit for students (2 books max)
        $maxBooks = $user->getMaxBooksAllowed();
        if (!$user->canBorrowMoreBooks()) {
            return redirect()->back()->with('error', ucfirst($user->member_type ?? 'user') . ' has reached the maximum book limit (' . $maxBooks . ' books). Please return a book before issuing a new one.');
        }

        // Check if user already has this book
        $existingBorrow = Borrow::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->where('status', 'borrowed')
            ->first();

        if ($existingBorrow) {
            return redirect()->back()->with('error', 'User already has this book borrowed!');
        }

        // Check if user already has a book with the same subject (only 1 book per subject allowed)
        if ($book->subject) {
            $sameSubjectBorrow = Borrow::where('user_id', $user->id)
                ->where('status', 'borrowed')
                ->whereHas('book', function($query) use ($book) {
                    $query->where('subject', $book->subject);
                })
                ->first();

            if ($sameSubjectBorrow) {
                return redirect()->back()->with('error', 'Student already has a book with subject "' . $book->subject . '" borrowed. A student can only borrow 1 book per subject. Please return the existing book first.');
            }
        }

        // Get library settings for issue duration
        $library = $book->library;
        $issueDurationDays = 14; // Default
        if ($library && $library->settings) {
            $issueDurationDays = $library->settings->book_issue_duration_days ?? 14;
        }
        
        // Calculate due date: Start counting from next day after issue date
        $borrowDate = now();
        $dueDate = $borrowDate->copy()->addDay()->addDays($issueDurationDays - 1);
        
        // Get fine per day based on issue duration
        $finePerDay = \App\Helpers\FineHelper::getFinePerDayByDuration($issueDurationDays);

        DB::transaction(function () use ($book, $user, $borrowDate, $dueDate, $issueDurationDays, $finePerDay) {
            Borrow::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'borrow_date' => $borrowDate,
                'issue_duration_days' => $issueDurationDays,
                'due_date' => $dueDate,
                'fine_per_day' => $finePerDay,
                'status' => 'borrowed',
                'issued_by' => auth()->id(),
            ]);

            $book->decrement('available_copies');
        });

        return redirect()->route('staff.borrows.index')
            ->with('success', 'Book issued successfully to ' . $user->name . ' via scanner!');
    }

    public function returnByScan(Request $request)
    {
        $request->validate([
            'book_code' => 'required|string',
        ]);

        // Find book
        $book = Book::where('isbn', $request->book_code)
            ->orWhere('id', $request->book_code)
            ->first();

        if (!$book) {
            return redirect()->back()->with('error', 'Book not found!');
        }

        // Find active borrow for this book
        $borrow = Borrow::where('book_id', $book->id)
            ->where('status', 'borrowed')
            ->latest()
            ->first();

        if (!$borrow) {
            return redirect()->back()->with('error', 'No active borrow found for this book!');
        }

        return redirect()->route('staff.borrows.return.show', $borrow)
            ->with('info', 'Book scanned. Please confirm return details.');
    }
}
