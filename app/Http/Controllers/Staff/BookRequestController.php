<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\BookRequest;
use App\Models\Borrow;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\BookRequestApproved;

class BookRequestController extends Controller
{
    public function index()
    {
        $requests = BookRequest::with(['user', 'book', 'approvedBy'])
            ->latest()
            ->get();
        
        $pendingCount = BookRequest::where('status', 'pending')->count();
        $approvedCount = BookRequest::where('status', 'approved')->count();

        return view('staff.book-requests.index', compact('requests', 'pendingCount', 'approvedCount'));
    }

    public function approve(BookRequest $request)
    {
        if ($request->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Request is not pending!');
        }

        $book = $request->book;
        
        if (!$book->isAvailable()) {
            return redirect()->back()
                ->with('error', 'Book is not available!');
        }

        DB::transaction(function () use ($request, $book) {
            $request->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Create in-app notification
            \App\Helpers\NotificationHelper::createNotification(
                $request->user_id,
                'book_approved',
                'Book Request Approved',
                'Your request for "' . $book->title . '" has been approved. You can collect it from the library.',
                route('student.my-books')
            );

            // Send email notification
            $request->user->notify(new BookRequestApproved($book, $request));
        });

        return redirect()->back()
            ->with('success', 'Book request approved! You can now issue the book to the student.');
    }

    public function reject(BookRequest $request)
    {
        if ($request->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Request is not pending!');
        }

        $request->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Create in-app notification
        \App\Helpers\NotificationHelper::createNotification(
            $request->user_id,
            'book_rejected',
            'Book Request Rejected',
            'Your request for "' . $request->book->title . '" has been rejected.',
            route('student.books.index')
        );

        return redirect()->back()
            ->with('success', 'Book request rejected.');
    }

    public function issue(BookRequest $request)
    {
        if ($request->status !== 'approved') {
            return redirect()->back()
                ->with('error', 'Request must be approved first!');
        }

        $book = $request->book;
        $user = $request->user;

        if (!$book->isAvailable()) {
            return redirect()->back()
                ->with('error', 'Book is not available!');
        }

        // Check if user has valid library card
        if (!$user->hasValidLibraryCard()) {
            return redirect()->back()
                ->with('error', 'Student does not have a valid library card! Please issue a library card first.');
        }

        // Check book limit based on member type
        $maxBooks = $user->getMaxBooksAllowed();
        if (!$user->canBorrowMoreBooks()) {
            return redirect()->back()
                ->with('error', ucfirst($user->member_type ?? 'user') . ' has reached the maximum book limit (' . $maxBooks . ' books). Please return a book before issuing a new one.');
        }

        // Check if user already has this book borrowed
        $existingBorrow = Borrow::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->where('status', 'borrowed')
            ->first();

        if ($existingBorrow) {
            return redirect()->back()
                ->with('error', 'User already has this book borrowed!');
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
                return redirect()->back()
                    ->with('error', 'Student already has a book with subject "' . $book->subject . '" borrowed. A student can only borrow 1 book per subject. Please return the existing book first.');
            }
        }

        DB::transaction(function () use ($request, $book, $user) {
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
            
            // Create borrow record
            $borrow = Borrow::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'borrow_date' => $borrowDate,
                'issue_duration_days' => $issueDurationDays,
                'due_date' => $dueDate,
                'fine_per_day' => $finePerDay,
                'status' => 'borrowed',
                'issued_by' => auth()->id(),
            ]);

            // Decrement available copies
            $book->decrement('available_copies');

            // Update request status
            $request->update([
                'status' => 'issued',
            ]);

            // Create in-app notification
            \App\Helpers\NotificationHelper::createNotification(
                $user->id,
                'book_issued',
                'Book Issued',
                'The book "' . $book->title . '" has been issued to you. Due date: ' . $borrow->due_date->format('M d, Y'),
                route('student.my-books')
            );
        });

        return redirect()->route('staff.borrows.index')
            ->with('success', 'Book issued successfully to ' . $user->name . '!');
    }
}
