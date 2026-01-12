<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrow;
use App\Models\Fine;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    // Total Books Report
    public function totalBooks(Request $request)
    {
        $query = Book::with(['author', 'category']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $books = $query->latest()->get();
        $totalBooks = Book::count();
        $availableBooks = Book::where('status', 'available')->where('available_copies', '>', 0)->count();
        $unavailableBooks = Book::where('status', 'unavailable')->orWhere('available_copies', 0)->count();

        return view('admin.reports.total-books', compact('books', 'totalBooks', 'availableBooks', 'unavailableBooks'));
    }

    // Book Issue Report
    public function bookIssue(Request $request)
    {
        $query = Borrow::with(['user', 'book']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->where('borrow_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('borrow_date', '<=', $request->to_date);
        }

        $borrows = $query->latest()->get();
        
        $totalIssued = Borrow::where('status', 'borrowed')->count();
        $totalReturned = Borrow::where('status', 'returned')->count();

        return view('admin.reports.book-issue', compact('borrows', 'totalIssued', 'totalReturned'));
    }

    // Overdue Report
    public function overdue(Request $request)
    {
        $query = Borrow::with(['user', 'book'])
            ->where('status', 'borrowed')
            ->where('due_date', '<', now());

        if ($request->filled('from_date')) {
            $query->where('due_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('due_date', '<=', $request->to_date);
        }

        $overdueBorrows = $query->latest('due_date')->get();
        
        $totalOverdue = Borrow::where('status', 'borrowed')
            ->where('due_date', '<', now())
            ->count();

        return view('admin.reports.overdue', compact('overdueBorrows', 'totalOverdue'));
    }

    // Fine Report
    public function fines(Request $request)
    {
        $query = Fine::with(['user', 'borrow.book']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $fines = $query->latest()->get();
        
        $totalPending = Fine::where('status', 'pending')->sum('amount');
        $totalPaid = Fine::where('status', 'paid')->sum('amount');
        $totalFines = Fine::sum('amount');

        return view('admin.reports.fines', compact('fines', 'totalPending', 'totalPaid', 'totalFines'));
    }

    // Student-wise Report
    public function studentWise(Request $request)
    {
        $query = User::where('role', 'student')->withCount(['borrows', 'fines']);

        if ($request->filled('student_id')) {
            $query->where('student_id', 'like', '%' . $request->student_id . '%');
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $students = $query->latest()->get();

        // Get detailed stats for each student
        foreach ($students as $student) {
            $student->active_borrows = Borrow::where('user_id', $student->id)
                ->where('status', 'borrowed')
                ->count();
            $student->total_borrows = Borrow::where('user_id', $student->id)->count();
            $student->pending_fines = Fine::where('user_id', $student->id)
                ->where('status', 'pending')
                ->sum('amount');
            $student->total_fines = Fine::where('user_id', $student->id)->sum('amount');
        }

        return view('admin.reports.student-wise', compact('students'));
    }

    // Student Detail Report
    public function studentDetail(User $user)
    {
        $user->load(['borrows.book', 'fines.borrow.book']);
        
        $activeBorrows = $user->borrows()->where('status', 'borrowed')->get();
        $returnedBorrows = $user->borrows()->where('status', 'returned')->get();
        $pendingFines = $user->fines()->where('status', 'pending')->get();
        $paidFines = $user->fines()->where('status', 'paid')->get();

        return view('admin.reports.student-detail', compact('user', 'activeBorrows', 'returnedBorrows', 'pendingFines', 'paidFines'));
    }

    // Popular Books Report
    public function popularBooks(Request $request)
    {
        $query = Book::with(['author', 'category'])
            ->withCount('borrows')
            ->orderBy('borrows_count', 'desc');

        if ($request->filled('from_date')) {
            $query->whereHas('borrows', function($q) use ($request) {
                $q->where('borrow_date', '>=', $request->from_date);
            });
        }

        if ($request->filled('to_date')) {
            $query->whereHas('borrows', function($q) use ($request) {
                $q->where('borrow_date', '<=', $request->to_date);
            });
        }

        $books = $query->take(50)->get();

        return view('admin.reports.popular-books', compact('books'));
    }

    // Member Activity Report
    public function memberActivity(Request $request)
    {
        $query = User::withCount(['borrows', 'fines'])
            ->whereIn('role', ['student', 'staff']);

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('from_date')) {
            $query->whereHas('borrows', function($q) use ($request) {
                $q->where('borrow_date', '>=', $request->from_date);
            });
        }

        if ($request->filled('to_date')) {
            $query->whereHas('borrows', function($q) use ($request) {
                $q->where('borrow_date', '<=', $request->to_date);
            });
        }

        $members = $query->orderBy('borrows_count', 'desc')->get();

        // Add activity stats
        foreach ($members as $member) {
            $member->active_borrows = Borrow::where('user_id', $member->id)
                ->where('status', 'borrowed')
                ->count();
            $member->returned_borrows = Borrow::where('user_id', $member->id)
                ->where('status', 'returned')
                ->count();
            $member->overdue_borrows = Borrow::where('user_id', $member->id)
                ->where('status', 'borrowed')
                ->where('due_date', '<', now())
                ->count();
        }

        return view('admin.reports.member-activity', compact('members'));
    }

    // User Issue History
    public function userIssueHistory(User $user)
    {
        $borrows = Borrow::with(['book.author', 'book.category'])
            ->where('user_id', $user->id)
            ->orderBy('borrow_date', 'desc')
            ->get();

        $stats = [
            'total_borrows' => $borrows->count(),
            'active_borrows' => $borrows->where('status', 'borrowed')->count(),
            'returned_borrows' => $borrows->where('status', 'returned')->count(),
            'overdue_borrows' => $borrows->where('status', 'borrowed')
                ->filter(function($borrow) {
                    return $borrow->due_date < now();
                })->count(),
        ];

        return view('admin.reports.user-issue-history', compact('user', 'borrows', 'stats'));
    }
}
