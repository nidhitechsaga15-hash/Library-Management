<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Borrow;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'active_borrows' => Borrow::where('status', 'borrowed')->count(),
            'overdue_borrows' => Borrow::where('status', 'borrowed')
                ->where('due_date', '<', now())
                ->count(),
            'total_students' => User::where('role', 'student')->count(),
            'available_books' => Book::where('status', 'available')->where('available_copies', '>', 0)->count(),
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

        return view('staff.dashboard', compact('stats', 'recent_borrows', 'alerts', 'overdueBorrows'));
    }
}
