<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\User;
use App\Models\Borrow;
use App\Models\BookRequest;
use App\Models\Fine;
use Illuminate\Http\Request;

class MobileDashboardController extends Controller
{
    protected function getUserFromToken(Request $request)
    {
        $token = $request->bearerToken() ?? $request->header('Authorization');
        if (!$token) {
            return null;
        }
        
        try {
            $decoded = base64_decode($token);
            $parts = explode('|', $decoded);
            if (count($parts) !== 3) {
                return null;
            }
            
            return User::find($parts[0]);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function adminDashboard(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
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

        $recent_borrows = Borrow::with(['user', 'book'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($borrow) {
                return [
                    'id' => $borrow->id,
                    'user' => [
                        'name' => $borrow->user->name,
                        'email' => $borrow->user->email,
                    ],
                    'book' => [
                        'title' => $borrow->book->title,
                    ],
                    'borrow_date' => $borrow->borrow_date,
                    'due_date' => $borrow->due_date,
                    'status' => $borrow->status,
                ];
            });

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'recent_borrows' => $recent_borrows,
        ]);
    }

    public function adminBooks(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 100);
        
        $query = Book::with(['author', 'category'])->latest();
        
        // Apply search filter if provided
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('isbn', 'like', '%' . $search . '%')
                  ->orWhereHas('author', function($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%');
                  });
            });
        }
        
        $paginated = $query->paginate($perPage, ['*'], 'page', $page);
        
        $books = $paginated->map(function ($book) {
            return [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author->name ?? 'Unknown',
                'category' => $book->category->name ?? 'Uncategorized',
                'available_copies' => $book->available_copies,
                'total_copies' => $book->total_copies,
                'status' => $book->status,
            ];
        });

        return response()->json([
            'success' => true,
            'books' => $books,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'has_more' => $paginated->hasMorePages(),
            ],
        ]);
    }

    public function staffDashboard(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'staff') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $stats = [
            'pending_requests' => BookRequest::where('status', 'pending')->count(),
            'overdue_books' => Borrow::where('status', 'borrowed')
                ->where('due_date', '<', now())
                ->count(),
            'active_borrows' => Borrow::where('status', 'borrowed')->count(),
            'total_students' => User::where('role', 'student')->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    public function staffStudents(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'staff') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $students = User::where('role', 'student')
            ->latest()
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'student_id' => $student->student_id,
                    'course' => $student->course,
                    'year' => $student->year,
                    'batch' => $student->batch,
                ];
            });

        return response()->json([
            'success' => true,
            'students' => $students,
        ]);
    }

    public function studentDashboard(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'student') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $stats = [
            'issued_books' => Borrow::where('user_id', $user->id)
                ->where('status', 'borrowed')
                ->count(),
            'pending_fines' => Fine::where('user_id', $user->id)
                ->where('status', 'pending')
                ->sum('amount'),
            'reservations' => \App\Models\BookReservation::where('user_id', $user->id)
                ->where('status', 'active')
                ->count(),
            'available_books' => Book::where('status', 'available')
                ->where('available_copies', '>', 0)
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    public function studentBooks(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'student') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $query = Book::with(['author', 'category'])
            ->where('status', 'available')
            ->where('available_copies', '>', 0);

        // Filter by student's course, batch, semester, and year
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

        $books = $query->latest()->get()->map(function ($book) {
            return [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author->name,
                'category' => $book->category->name,
                'available_copies' => $book->available_copies,
                'isbn' => $book->isbn,
            ];
        });

        return response()->json([
            'success' => true,
            'books' => $books,
        ]);
    }
}
