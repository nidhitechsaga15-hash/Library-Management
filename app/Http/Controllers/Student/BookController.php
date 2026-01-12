<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Book::with(['author', 'category'])
            ->where('status', 'available')
            ->where('available_copies', '>', 0);

        // Default filter by student's course, batch, semester, and year
        if ($user->course) {
            $query->where('course', $user->course);
        }
        if ($user->batch) {
            $query->where('batch', $user->batch);
        }
        if ($user->semester) {
            $query->where('semester', $user->semester);
        }
        if ($user->year) {
            $query->where('year', $user->year);
        }

        // Show recommended books based on course, batch, semester, and year
        $recommendedBooks = collect();
        if ($user->course && $user->semester) {
            $recommendedQuery = Book::with(['author', 'category'])
                ->where('status', 'available')
                ->where('available_copies', '>', 0)
                ->where(function($q) use ($user) {
                    $q->where('course', $user->course)
                      ->where('semester', $user->semester);
                    if ($user->batch) {
                        $q->where('batch', $user->batch);
                    }
                    if ($user->year) {
                        $q->where('year', $user->year);
                    }
                });
            $recommendedBooks = $recommendedQuery->latest()->get();
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('isbn', 'like', '%' . $request->search . '%')
                  ->orWhereHas('author', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by course/batch/semester/year if selected
        if ($request->filled('course')) {
            $query->where('course', $request->course);
        }
        if ($request->filled('batch')) {
            $query->where('batch', $request->batch);
        }
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $books = $query->latest()->get();

        $categories = \App\Models\Category::orderBy('name')->get();

        return view('student.books.index', compact('books', 'categories', 'recommendedBooks'));
    }

    public function show(Book $book)
    {
        $book->load(['author', 'category']);
        return view('student.books.show', compact('book'));
    }

    public function search(Request $request)
    {
        $query = Book::with(['author', 'category'])
            ->where('status', 'available')
            ->where('available_copies', '>', 0);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('isbn', 'like', '%' . $search . '%')
                  ->orWhere('publisher', 'like', '%' . $search . '%')
                  ->orWhereHas('author', function($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $books = $query->latest()->paginate(12);
        $categories = \App\Models\Category::orderBy('name')->get();

        return view('student.books.search', compact('books', 'categories'));
    }

    public function request(Book $book)
    {
        $user = auth()->user();

        // Check book limit (2 books max for students)
        $activeBorrows = $user->getActiveBorrowsCount();
        $maxBooks = 2;
        
        if ($activeBorrows >= $maxBooks) {
            return back()->with('error', 'आपने पहले से ही ' . $activeBorrows . ' books issue कर रखी हैं। आप अधिकतम ' . $maxBooks . ' books issue कर सकते हैं। कृपया पहले एक book return करें, फिर नई book request करें।');
        }

        // Check if book is available
        if (!$book->isAvailable()) {
            return back()->with('error', 'Book is not available for request!');
        }

        // Check if user already has a request for this book (pending, hold, or approved)
        $existingRequest = \App\Models\BookRequest::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->whereIn('status', ['pending', 'hold', 'approved'])
            ->first();

        if ($existingRequest) {
            $statusText = $existingRequest->status === 'hold' ? 'on hold' : ($existingRequest->status === 'approved' ? 'approved' : 'pending');
            return back()->with('duplicate_request', 'You already have a ' . $statusText . ' request for this book! Please wait for the current request to be processed.');
        }

        // Check if user already has this book borrowed
        $existingBorrow = \App\Models\Borrow::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->where('status', 'borrowed')
            ->first();

        if ($existingBorrow) {
            return back()->with('error', 'You already have this book borrowed!');
        }

        // Check if user already has a book with the same subject (only 1 book per subject allowed)
        if ($book->subject) {
            $sameSubjectBorrow = \App\Models\Borrow::where('user_id', $user->id)
                ->where('status', 'borrowed')
                ->whereHas('book', function($query) use ($book) {
                    $query->where('subject', $book->subject);
                })
                ->first();

            if ($sameSubjectBorrow) {
                return back()->with('error', 'आपने पहले से ही "' . $book->subject . '" subject की एक book issue कर रखी है। एक student एक subject की केवल 1 book ही ले सकता है। कृपया पहले वह book return करें, फिर नई book request करें।');
            }
        }

        // Get library settings for hold deadline
        $library = $book->library;
        $holdDeadlineDays = 2; // Default 2 days
        if ($library && $library->settings) {
            $holdDeadlineDays = $library->settings->book_collection_deadline_days ?? 2;
        }

        // 🔥 AUTO HOLD SYSTEM: If stock available, set status to hold (NO stock deduction yet)
        $status = 'pending';
        $stockDeducted = false;
        $holdExpiresAt = null;

        if ($book->available_copies > 0) {
            // Auto hold - set status to hold but DON'T deduct stock yet
            // Stock will be deducted only when admin approves
            $status = 'hold';
            $stockDeducted = false; // Stock not deducted yet
            $holdExpiresAt = now()->addDays($holdDeadlineDays);
        }

        $bookRequest = \App\Models\BookRequest::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => $status,
            'stock_deducted' => $stockDeducted,
            'stock_deducted_at' => $stockDeducted ? now() : null,
            'hold_expires_at' => $holdExpiresAt,
        ]);

        // Notify all admins and staff
        $message = $status === 'hold' 
            ? $user->name . ' has requested the book "' . $book->title . '" - ON HOLD (Waiting for approval)'
            : $user->name . ' has requested the book "' . $book->title . '" - Pending (No stock available)';
        
        \App\Helpers\NotificationHelper::notifyAdminsAndStaff(
            'book_request',
            'New Book Request',
            $message,
            route('admin.book-requests.index')
        );

        // Notify student
        if ($status === 'hold') {
            \App\Helpers\NotificationHelper::createNotification(
                $user->id,
                'book_hold',
                'Book on Hold',
                'Your request for "' . $book->title . '" is on hold. Waiting for admin approval. Stock will be reserved after approval.',
                route('student.my-books')
            );
        }

        $successMessage = $status === 'hold'
            ? 'Book request submitted and placed on HOLD! Waiting for admin approval. Stock will be reserved after approval.'
            : 'Book request submitted successfully! Staff will review when stock becomes available.';

        return back()->with('success', $successMessage);
    }
}
