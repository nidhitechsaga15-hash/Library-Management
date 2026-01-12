<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrow;
use App\Models\User;
use App\Models\Fine;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_books' => Book::count(),
            'issued_books' => Borrow::where('status', 'borrowed')->count(),
            'returned_books' => Borrow::where('status', 'returned')->count(),
            'overdue_books' => Borrow::where('status', 'borrowed')
                ->where('due_date', '<', now())
                ->count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_staff' => User::where('role', 'staff')->count(),
            'available_books' => Book::where('status', 'available')->where('available_copies', '>', 0)->count(),
            'pending_fines' => Fine::where('status', 'pending')->sum('amount'),
        ];

        // Alerts for dashboard
        $alerts = [
            'books_due_today' => Borrow::where('status', 'borrowed')
                ->whereDate('due_date', now())
                ->count(),
            'books_due_tomorrow' => Borrow::where('status', 'borrowed')
                ->whereDate('due_date', now()->addDay())
                ->count(),
            'overdue_books' => Borrow::where('status', 'borrowed')
                ->where('due_date', '<', now())
                ->count(),
            'pending_fines_amount' => Fine::where('status', 'pending')->sum('amount'),
        ];

        // Get overdue books for alerts
        $overdueBorrows = Borrow::with(['user', 'book'])
            ->where('status', 'borrowed')
            ->where('due_date', '<', now())
            ->latest('due_date')
            ->take(5)
            ->get();

        $recent_borrows = Borrow::with(['user', 'book'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent_borrows', 'alerts', 'overdueBorrows'));
    }
}
