<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookReservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function store(Request $request, Book $book)
    {
        $user = auth()->user();

        // Check if book is already available
        if ($book->isAvailable()) {
            return back()->with('error', 'Book is available! You can request it directly instead of reserving.');
        }

        // Check if user already has an active reservation for this book
        $existingReservation = BookReservation::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->whereIn('status', ['pending', 'available'])
            ->first();

        if ($existingReservation) {
            return back()->with('error', 'You already have an active reservation for this book!');
        }

        // Check if user already has this book borrowed
        $existingBorrow = \App\Models\Borrow::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->where('status', 'borrowed')
            ->first();

        if ($existingBorrow) {
            return back()->with('error', 'You already have this book borrowed!');
        }

        $reservation = BookReservation::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'pending',
            'reserved_at' => now(),
            'expires_at' => now()->addDays(7), // Reservation expires in 7 days
        ]);

        return back()->with('success', 'Book reserved successfully! You will be notified when the book becomes available.');
    }

    public function cancel(BookReservation $reservation)
    {
        if ($reservation->user_id !== auth()->id()) {
            return back()->with('error', 'Unauthorized action!');
        }

        if ($reservation->status === 'issued') {
            return back()->with('error', 'Cannot cancel a reservation that has been issued!');
        }

        $reservation->update(['status' => 'cancelled']);

        return back()->with('success', 'Reservation cancelled successfully!');
    }

    public function index()
    {
        $reservations = BookReservation::where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'available'])
            ->with('book')
            ->latest()
            ->get();

        return view('student.reservations.index', compact('reservations'));
    }
}
