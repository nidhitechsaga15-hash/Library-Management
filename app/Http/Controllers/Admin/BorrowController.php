<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrow;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowController extends Controller
{
    public function index()
    {
        $borrows = Borrow::with(['user', 'book', 'fine'])
            ->latest()
            ->get();
        
        return view('admin.borrows.index', compact('borrows'));
    }

    public function create()
    {
        // Load all books but optimize query
        $books = Book::with('author')
            ->select('id', 'title', 'isbn', 'available_copies', 'author_id')
            ->orderBy('title')
            ->get();
        
        return view('admin.borrows.create', compact('books'));
    }

    public function issue(Request $request)
    {
        $validated = $request->validate([
            'user_identifier' => 'required|string',
            'book_id' => 'required|exists:books,id',
            'borrow_date' => 'required|date',
            'issue_duration_days' => 'required|integer|min:1|max:365',
            'fine_per_day' => 'required|numeric|min:0',
        ]);

        // Find user by student_id or staff_id or email
        $user = User::where('student_id', $validated['user_identifier'])
            ->orWhere('staff_id', $validated['user_identifier'])
            ->orWhere('email', $validated['user_identifier'])
            ->first();

        if (!$user) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'User not found with the provided ID/Email!');
        }

        if (!$user->is_active) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'User account is inactive!');
        }

        // Check if user has valid library card (only for students)
        if ($user->isStudent() && !$user->hasValidLibraryCard()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'User does not have a valid library card! Please issue a library card first.');
        }

        $book = Book::findOrFail($validated['book_id']);
        
        if ($book->available_copies <= 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Book is not available for borrowing!');
        }

        // Check book limit based on member type
        $maxBooks = $user->getMaxBooksAllowed();
        if (!$user->canBorrowMoreBooks()) {
            return redirect()->back()
                ->withInput()
                ->with('error', ucfirst($user->member_type ?? 'user') . ' has reached the maximum book limit (' . $maxBooks . ' books). Please return a book before issuing a new one.');
        }

        // Check if user already has this book borrowed
        $existingBorrow = Borrow::where('user_id', $user->id)
            ->where('book_id', $validated['book_id'])
            ->where('status', 'borrowed')
            ->first();

        if ($existingBorrow) {
            return redirect()->back()
                ->withInput()
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
                    ->withInput()
                    ->with('error', 'Student already has a book with subject "' . $book->subject . '" borrowed. A student can only borrow 1 book per subject. Please return the existing book first.');
            }
        }

        // Calculate due date: Start counting from next day after issue date
        $borrowDate = \Carbon\Carbon::parse($validated['borrow_date']);
        $dueDate = $borrowDate->copy()->addDay()->addDays($validated['issue_duration_days'] - 1);
        
        // Use manual fine per day from form
        $finePerDay = (float) $validated['fine_per_day'];

        $borrow = Borrow::create([
            'user_id' => $user->id,
            'book_id' => $validated['book_id'],
            'borrow_date' => $borrowDate,
            'issue_duration_days' => $validated['issue_duration_days'],
            'due_date' => $dueDate,
            'fine_per_day' => $finePerDay,
            'status' => 'borrowed',
            'issued_by' => auth()->id(),
        ]);

        $book->decrement('available_copies');

        // Create notification for student
        if ($user->isStudent()) {
            \App\Helpers\NotificationHelper::createNotification(
                $user->id,
                'book_issued',
                'Book Issued',
                'The book "' . $book->title . '" has been issued to you. Due date: ' . $borrow->due_date->format('M d, Y'),
                route('student.my-books')
            );
        }

        return redirect()->route('admin.borrows.index')
            ->with('success', 'Book issued successfully to ' . $user->name . '!');
    }

    public function showReturn(Borrow $borrow)
    {
        if ($borrow->status === 'returned') {
            return redirect()->route('admin.borrows.index')
                ->with('error', 'Book already returned!');
        }

        $returnDate = now();
        $fineAmount = 0;
        $daysOverdue = 0;

        // Calculate fine if overdue
        if ($borrow->due_date < $returnDate) {
            $daysOverdue = $returnDate->diffInDays($borrow->due_date);
            // Use the fine_per_day stored at issue time, or calculate based on issue duration
            $finePerDay = $borrow->fine_per_day ?? \App\Helpers\FineHelper::getFinePerDayByDuration($borrow->issue_duration_days ?? 15);
            $fineAmount = $daysOverdue * $finePerDay;
        }

        $borrow->load(['user', 'book']);

        return view('admin.borrows.return', compact('borrow', 'fineAmount', 'daysOverdue', 'returnDate'));
    }

    /**
     * Extend due date for a borrow
     */
    public function extend(Request $request, Borrow $borrow)
    {
        if ($borrow->status === 'returned') {
            return redirect()->back()
                ->with('error', 'Cannot extend due date for returned book!');
        }

        $validated = $request->validate([
            'additional_days' => 'required|integer|min:1|max:30',
        ]);

        $oldDueDate = $borrow->due_date->copy();
        $borrow->extendDueDate($validated['additional_days']);

        // Notify student
        \App\Helpers\NotificationHelper::createNotification(
            $borrow->user_id,
            'book_due_date_extended',
            'Due Date Extended',
            'The due date for "' . $borrow->book->title . '" has been extended from ' . $oldDueDate->format('M d, Y') . ' to ' . $borrow->due_date->format('M d, Y'),
            route('student.my-books')
        );

        return redirect()->back()
            ->with('success', 'Due date extended successfully! New due date: ' . $borrow->due_date->format('M d, Y'));
    }

    public function return(Borrow $borrow)
    {
        if ($borrow->status === 'returned') {
            return redirect()->back()
                ->with('error', 'Book already returned!');
        }

        $returnDate = now();
        $fineAmount = 0;
        $daysOverdue = 0;

        // Calculate fine if overdue
        if ($borrow->due_date < $returnDate) {
            $daysOverdue = $returnDate->diffInDays($borrow->due_date);
            // Use the fine_per_day stored at issue time, or calculate based on issue duration
            $finePerDay = $borrow->fine_per_day ?? \App\Helpers\FineHelper::getFinePerDayByDuration($borrow->issue_duration_days ?? 15);
            $fineAmount = $daysOverdue * $finePerDay;
        }

        $borrow->update([
            'status' => 'returned',
            'return_date' => $returnDate,
        ]);

        $book = $borrow->book;
        $book->increment('available_copies');

        // Check for pending reservations and notify the first in queue
        $reservation = \App\Models\BookReservation::where('book_id', $book->id)
            ->where('status', 'pending')
            ->orderBy('reserved_at', 'asc')
            ->first();

        if ($reservation) {
            $reservation->update([
                'status' => 'available',
                'notified_at' => now(),
            ]);

            // Notify student that reserved book is now available
            \App\Helpers\NotificationHelper::createNotification(
                $reservation->user_id,
                'book_reservation_available',
                'Reserved Book Available',
                'The book "' . $book->title . '" you reserved is now available. Please collect it within 3 days.',
                route('student.reservations.index')
            );
        }

        // Create fine if overdue
        if ($fineAmount > 0) {
            $fine = $borrow->fine()->create([
                'user_id' => $borrow->user_id,
                'amount' => $fineAmount,
                'reason' => "Overdue return - {$daysOverdue} day(s) late",
                'status' => 'pending',
            ]);

            // Notify student about fine
            \App\Helpers\NotificationHelper::createNotification(
                $borrow->user_id,
                'fine_added',
                'Fine Applied',
                'A fine of ₹' . $fineAmount . ' has been applied for returning "' . $borrow->book->title . '" ' . $daysOverdue . ' day(s) late.',
                route('student.fines.index')
            );
        }

        // Notify student about return
        \App\Helpers\NotificationHelper::createNotification(
            $borrow->user_id,
            'book_returned',
            'Book Returned',
            'You have successfully returned "' . $borrow->book->title . '".' . ($fineAmount > 0 ? ' Please check your fines.' : ''),
            route('student.my-books')
        );

        return redirect()->route('admin.borrows.index')
            ->with('success', $fineAmount > 0 ? "Book returned successfully! Fine of ₹{$fineAmount} has been applied for {$daysOverdue} day(s) overdue." : 'Book returned successfully!');
    }

    public function overdue()
    {
        $overdueBorrows = Borrow::with(['user', 'book', 'fine'])
            ->where('status', 'borrowed')
            ->where('due_date', '<', now())
            ->latest('due_date')
            ->get();
        
        $totalOverdue = Borrow::where('status', 'borrowed')
            ->where('due_date', '<', now())
            ->count();

        // Get fine mapping for display (using default for longest duration)
        $finePerDay = \App\Helpers\FineHelper::getFinePerDayByDuration(365);

        return view('admin.borrows.overdue', compact('overdueBorrows', 'totalOverdue', 'finePerDay'));
    }

}
