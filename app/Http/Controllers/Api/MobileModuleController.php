<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Author;
use App\Models\Category;
use App\Models\User;
use App\Models\Borrow;
use App\Models\Fine;
use App\Models\BookRequest;
use App\Models\BookReservation;
use App\Models\LibraryCard;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileModuleController extends Controller
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

    // ============ ADMIN MODULES ============

    // Authors
    public function adminAuthors(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $authors = Author::withCount('books')->latest()->get();
        return response()->json(['success' => true, 'authors' => $authors]);
    }

    public function adminCreateAuthor(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'nationality' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
        ]);
        
        $author = Author::create($request->all());
        return response()->json(['success' => true, 'author' => $author]);
    }

    public function adminGetAuthor(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $author = Author::withCount('books')->findOrFail($id);
        return response()->json(['success' => true, 'author' => $author]);
    }

    public function adminUpdateAuthor(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'nationality' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
        ]);
        
        $author = Author::findOrFail($id);
        $author->update($request->all());
        return response()->json(['success' => true, 'author' => $author]);
    }

    public function adminDeleteAuthor(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $author = Author::findOrFail($id);
        if ($author->books()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete author with books'], 400);
        }
        $author->delete();
        return response()->json(['success' => true, 'message' => 'Author deleted']);
    }

    // Categories
    public function adminCategories(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $categories = Category::withCount('books')->latest()->get();
        return response()->json(['success' => true, 'categories' => $categories]);
    }

    public function adminCreateCategory(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $category = Category::create($request->all());
        return response()->json(['success' => true, 'category' => $category]);
    }

    public function adminGetCategory(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $category = Category::withCount('books')->findOrFail($id);
        return response()->json(['success' => true, 'category' => $category]);
    }

    public function adminUpdateCategory(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $category = Category::findOrFail($id);
        $category->update($request->all());
        return response()->json(['success' => true, 'category' => $category]);
    }

    public function adminDeleteCategory(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $category = Category::findOrFail($id);
        if ($category->books()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete category with books'], 400);
        }
        $category->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted']);
    }

    // Users
    public function adminUsers(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $users = User::latest()->get();
        return response()->json(['success' => true, 'users' => $users]);
    }

    public function adminCreateUser(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,staff,student',
            'student_id' => 'nullable|string|max:255|unique:users',
            'course' => 'nullable|string|max:255',
            'batch' => 'nullable|string|max:255',
            'semester' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
        ]);
        
        $userData = $request->all();
        $userData['password'] = bcrypt($request->password);
        $newUser = User::create($userData);
        return response()->json(['success' => true, 'user' => $newUser]);
    }

    public function adminGetUser(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $targetUser = User::findOrFail($id);
        return response()->json(['success' => true, 'user' => $targetUser]);
    }

    public function adminUpdateUser(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:admin,staff,student',
            'student_id' => 'nullable|string|max:255|unique:users,student_id,' . $id,
            'course' => 'nullable|string|max:255',
            'batch' => 'nullable|string|max:255',
            'semester' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
        ]);
        
        $targetUser = User::findOrFail($id);
        $targetUser->update($request->except('password'));
        return response()->json(['success' => true, 'user' => $targetUser]);
    }

    public function adminDeleteUser(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        if ($user->id == $id) {
            return response()->json(['success' => false, 'message' => 'Cannot delete yourself'], 400);
        }
        
        $targetUser = User::findOrFail($id);
        $targetUser->delete();
        return response()->json(['success' => true, 'message' => 'User deleted']);
    }

    // Books
    public function adminCreateBook(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        try {
            $validated = $request->validate([
                'isbn' => 'required|string|unique:books,isbn',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'author_id' => 'required|integer|exists:authors,id',
                'category_id' => 'required|integer|exists:categories,id',
                'publisher' => 'nullable|string|max:255',
                'edition' => 'nullable|string|max:50',
                'publication_year' => 'nullable|integer|min:1000|max:' . date('Y'),
                'total_copies' => 'required|integer|min:1',
                'available_copies' => 'required|integer|min:0',
                'rack_number' => 'nullable|string|max:50',
                'language' => 'nullable|string|max:50',
                'pages' => 'nullable|integer|min:1',
                'status' => 'required|in:available,unavailable',
            ]);

            $book = Book::create($validated);
            
            // Reload with relationships to ensure they're loaded
            $book = $book->fresh(['author', 'category']);
            
            return response()->json([
                'success' => true,
                'book' => [
                    'id' => $book->id,
                    'isbn' => $book->isbn,
                    'title' => $book->title,
                    'author_id' => $book->author_id,
                    'category_id' => $book->category_id,
                    'author' => $book->author ? $book->author->name : 'Unknown',
                    'category' => $book->category ? $book->category->name : 'Unknown',
                    'available_copies' => $book->available_copies,
                    'total_copies' => $book->total_copies,
                    'status' => $book->status,
                    'publisher' => $book->publisher,
                    'edition' => $book->edition,
                    'publication_year' => $book->publication_year,
                    'rack_number' => $book->rack_number,
                    'language' => $book->language,
                    'pages' => $book->pages,
                    'description' => $book->description,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Create book error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create book: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function adminGetBook(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $book = Book::with(['author', 'category'])->findOrFail($id);
        return response()->json(['success' => true, 'book' => $book]);
    }

    public function adminUpdateBook(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $book = Book::findOrFail($id);
        
        $request->validate([
            'isbn' => 'required|string|unique:books,isbn,' . $id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'author_id' => 'required|exists:authors,id',
            'category_id' => 'required|exists:categories,id',
            'publisher' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:50',
            'publication_year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'total_copies' => 'required|integer|min:1',
            'available_copies' => 'required|integer|min:0',
            'rack_number' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:50',
            'pages' => 'nullable|integer|min:1',
            'status' => 'required|in:available,unavailable',
        ]);
        
        $book->update($request->all());
        return response()->json(['success' => true, 'book' => $book->load(['author', 'category'])]);
    }

    public function adminDeleteBook(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $book = Book::findOrFail($id);
        if ($book->borrows()->where('status', 'borrowed')->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete book with active borrows'], 400);
        }
        $book->delete();
        return response()->json(['success' => true, 'message' => 'Book deleted']);
    }

    // Borrows
    public function adminBorrows(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $borrows = Borrow::with(['user', 'book.author', 'book.category'])
            ->latest()
            ->get();
        return response()->json(['success' => true, 'borrows' => $borrows]);
    }

    public function adminIssueBook(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'borrow_date' => 'nullable|date',
            'issue_duration_days' => 'required|integer|min:1|max:365',
        ]);
        
        $borrowUser = User::findOrFail($request->user_id);
        $book = Book::findOrFail($request->book_id);
        
        if ($book->available_copies <= 0) {
            return response()->json(['success' => false, 'message' => 'Book not available'], 400);
        }
        
        // Check if user has valid library card (only for students)
        if ($borrowUser->isStudent() && !$borrowUser->hasValidLibraryCard()) {
            return response()->json(['success' => false, 'message' => 'User does not have a valid library card'], 400);
        }
        
        // Check book limit based on member type
        if (!$borrowUser->canBorrowMoreBooks()) {
            $maxBooks = $borrowUser->getMaxBooksAllowed();
            return response()->json(['success' => false, 'message' => ucfirst($borrowUser->member_type ?? 'user') . ' has reached maximum book limit (' . $maxBooks . ' books)'], 400);
        }
        
        // Calculate due date: Start counting from next day after issue date
        $borrowDate = $request->borrow_date ? \Carbon\Carbon::parse($request->borrow_date) : now();
        $dueDate = $borrowDate->copy()->addDay()->addDays($request->issue_duration_days - 1);
        
        // Get fine per day based on issue duration
        $finePerDay = \App\Helpers\FineHelper::getFinePerDayByDuration($request->issue_duration_days);
        
        $borrow = Borrow::create([
            'user_id' => $request->user_id,
            'book_id' => $request->book_id,
            'borrow_date' => $borrowDate,
            'issue_duration_days' => $request->issue_duration_days,
            'due_date' => $dueDate,
            'fine_per_day' => $finePerDay,
            'status' => 'borrowed',
            'issued_by' => $user->id,
        ]);
        
        $book->decrement('available_copies');
        
        // Create notification for student
        if ($borrowUser->isStudent()) {
            \App\Helpers\NotificationHelper::createNotification(
                $borrowUser->id,
                'book_issued',
                'Book Issued',
                'The book "' . $book->title . '" has been issued to you. Due date: ' . $dueDate->format('M d, Y'),
                route('student.my-books')
            );
        }
        
        return response()->json(['success' => true, 'borrow' => $borrow->load(['user', 'book'])]);
    }

    public function adminReturnBook(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $borrow = Borrow::findOrFail($id);
        if ($borrow->status !== 'borrowed') {
            return response()->json(['success' => false, 'message' => 'Book already returned'], 400);
        }
        
        $borrow->update([
            'status' => 'returned',
            'return_date' => now(),
        ]);
        
        $borrow->book->increment('available_copies');
        
        // Calculate fine if overdue
        if ($borrow->due_date < now()) {
            $daysOverdue = now()->diffInDays($borrow->due_date);
            // Use the fine_per_day stored at issue time, or calculate based on issue duration
            $finePerDay = $borrow->fine_per_day ?? \App\Helpers\FineHelper::getFinePerDayByDuration($borrow->issue_duration_days ?? 15);
            $fineAmount = $daysOverdue * $finePerDay;
            
            Fine::create([
                'user_id' => $borrow->user_id,
                'borrow_id' => $borrow->id,
                'amount' => $fineAmount,
                'status' => 'pending',
                'reason' => "Overdue return - {$daysOverdue} day(s) late",
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
            'You have successfully returned "' . $borrow->book->title . '".',
            route('student.my-books')
        );
        
        return response()->json(['success' => true, 'borrow' => $borrow->load(['user', 'book'])]);
    }

    // Fines
    public function adminFines(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $fines = Fine::with(['user', 'borrow.book'])->latest()->get();
        return response()->json(['success' => true, 'fines' => $fines]);
    }

    public function adminUpdateFineStatus(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'status' => 'required|in:pending,paid,waived',
        ]);
        
        $fine = Fine::findOrFail($id);
        $fine->update(['status' => $request->status]);
        return response()->json(['success' => true, 'fine' => $fine->load(['user', 'borrow.book'])]);
    }

    // Book Requests
    public function adminBookRequests(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $requests = BookRequest::with(['user', 'book.author', 'book.category'])
            ->latest()
            ->get();
        
        $stats = [
            'pending_count' => BookRequest::where('status', 'pending')->count(),
            'approved_count' => BookRequest::where('status', 'approved')->count(),
            'total_requests' => BookRequest::count(),
            'issued_count' => BookRequest::where('status', 'issued')->count(),
        ];
        
        return response()->json(['success' => true, 'requests' => $requests, 'stats' => $stats]);
    }

    public function adminApproveRequest(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $bookRequest = BookRequest::findOrFail($id);
        $bookRequest->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
        
        return response()->json(['success' => true, 'request' => $bookRequest]);
    }

    public function adminRejectRequest(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $bookRequest = BookRequest::findOrFail($id);
        $bookRequest->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
        
        return response()->json(['success' => true, 'request' => $bookRequest]);
    }

    public function adminIssueRequest(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'due_date' => 'required|date|after:today',
        ]);
        
        $bookRequest = BookRequest::findOrFail($id);
        if ($bookRequest->status !== 'approved') {
            return response()->json(['success' => false, 'message' => 'Request must be approved first'], 400);
        }
        
        $book = $bookRequest->book;
        if ($book->available_copies <= 0) {
            return response()->json(['success' => false, 'message' => 'Book not available'], 400);
        }
        
        $borrow = Borrow::create([
            'user_id' => $bookRequest->user_id,
            'book_id' => $bookRequest->book_id,
            'borrow_date' => now(),
            'due_date' => $request->due_date,
            'status' => 'borrowed',
        ]);
        
        $book->decrement('available_copies');
        $bookRequest->update(['status' => 'issued']);
        
        return response()->json(['success' => true, 'borrow' => $borrow->load(['user', 'book'])]);
    }

    // Reports
    public function adminReports(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $reports = [
            'total_books' => Book::count(),
            'available_books' => Book::where('status', 'available')->where('available_copies', '>', 0)->count(),
            'total_borrows' => Borrow::count(),
            'active_borrows' => Borrow::where('status', 'borrowed')->count(),
            'overdue_borrows' => Borrow::where('status', 'borrowed')->where('due_date', '<', now())->count(),
            'total_fines' => Fine::sum('amount'),
            'pending_fines' => Fine::where('status', 'pending')->sum('amount'),
            'total_students' => User::where('role', 'student')->count(),
            'total_staff' => User::where('role', 'staff')->count(),
        ];
        
        return response()->json(['success' => true, 'reports' => $reports]);
    }

    public function adminTotalBooksReport(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
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
        
        return response()->json([
            'success' => true,
            'books' => $books,
            'stats' => [
                'total_books' => $totalBooks,
                'available_books' => $availableBooks,
                'unavailable_books' => $unavailableBooks,
            ],
        ]);
    }

    public function adminBookIssueReport(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
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
        
        return response()->json([
            'success' => true,
            'borrows' => $borrows,
            'stats' => [
                'total_issued' => $totalIssued,
                'total_returned' => $totalReturned,
            ],
        ]);
    }

    public function adminOverdueReport(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
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
        
        return response()->json([
            'success' => true,
            'overdue_borrows' => $overdueBorrows,
            'total_overdue' => $totalOverdue,
        ]);
    }

    public function adminFinesReport(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
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
        
        return response()->json([
            'success' => true,
            'fines' => $fines,
            'stats' => [
                'total_pending' => $totalPending,
                'total_paid' => $totalPaid,
                'total_fines' => $totalFines,
            ],
        ]);
    }

    public function adminStudentWiseReport(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
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
        
        return response()->json(['success' => true, 'students' => $students]);
    }

    public function adminStudentDetailReport(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $student = User::with(['borrows.book', 'fines.borrow.book'])->findOrFail($id);
        $activeBorrows = $student->borrows()->where('status', 'borrowed')->get();
        $returnedBorrows = $student->borrows()->where('status', 'returned')->get();
        $pendingFines = $student->fines()->where('status', 'pending')->get();
        $paidFines = $student->fines()->where('status', 'paid')->get();
        
        return response()->json([
            'success' => true,
            'student' => $student,
            'active_borrows' => $activeBorrows,
            'returned_borrows' => $returnedBorrows,
            'pending_fines' => $pendingFines,
            'paid_fines' => $paidFines,
        ]);
    }

    // Library Cards
    public function adminLibraryCards(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $cards = LibraryCard::with('user')->latest()->get();
        return response()->json(['success' => true, 'cards' => $cards]);
    }

    public function adminCreateLibraryCard(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:library_cards,user_id',
            'expiry_date' => 'required|date|after:today',
        ]);
        
        $card = LibraryCard::create($request->all());
        return response()->json(['success' => true, 'card' => $card->load('user')]);
    }

    public function adminBlockLibraryCard(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $card = LibraryCard::findOrFail($id);
        $card->update(['status' => 'blocked']);
        return response()->json(['success' => true, 'card' => $card->load('user')]);
    }

    public function adminUnblockLibraryCard(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $card = LibraryCard::findOrFail($id);
        $card->update(['status' => 'active']);
        return response()->json(['success' => true, 'card' => $card->load('user')]);
    }

    // ============ STAFF MODULES ============

    public function staffBooks(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'staff') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $books = Book::with(['author', 'category'])->latest()->get();
        return response()->json(['success' => true, 'books' => $books]);
    }

    public function staffBorrows(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'staff') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $borrows = Borrow::with(['user', 'book.author', 'book.category'])
            ->latest()
            ->get();
        return response()->json(['success' => true, 'borrows' => $borrows]);
    }

    public function staffIssueBook(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'staff') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'borrow_date' => 'nullable|date',
            'issue_duration_days' => 'required|integer|min:1|max:365',
        ]);
        
        $borrowUser = User::findOrFail($request->user_id);
        $book = Book::findOrFail($request->book_id);
        
        if ($book->available_copies <= 0) {
            return response()->json(['success' => false, 'message' => 'Book not available'], 400);
        }
        
        // Check if user has valid library card
        if ($borrowUser->isStudent() && !$borrowUser->hasValidLibraryCard()) {
            return response()->json(['success' => false, 'message' => 'Student does not have a valid library card'], 400);
        }
        
        // Check book limit based on member type
        if (!$borrowUser->canBorrowMoreBooks()) {
            $maxBooks = $borrowUser->getMaxBooksAllowed();
            return response()->json(['success' => false, 'message' => ucfirst($borrowUser->member_type ?? 'user') . ' has reached maximum book limit (' . $maxBooks . ' books)'], 400);
        }
        
        // Calculate due date: Start counting from next day after issue date
        $borrowDate = $request->borrow_date ? \Carbon\Carbon::parse($request->borrow_date) : now();
        $dueDate = $borrowDate->copy()->addDay()->addDays($request->issue_duration_days - 1);
        
        // Get fine per day based on issue duration
        $finePerDay = \App\Helpers\FineHelper::getFinePerDayByDuration($request->issue_duration_days);
        
        $borrow = Borrow::create([
            'user_id' => $request->user_id,
            'book_id' => $request->book_id,
            'borrow_date' => $borrowDate,
            'issue_duration_days' => $request->issue_duration_days,
            'due_date' => $dueDate,
            'fine_per_day' => $finePerDay,
            'status' => 'borrowed',
            'issued_by' => $user->id,
        ]);
        
        $book->decrement('available_copies');
        
        // Create notification for student
        \App\Helpers\NotificationHelper::createNotification(
            $borrowUser->id,
            'book_issued',
            'Book Issued',
            'The book "' . $book->title . '" has been issued to you. Due date: ' . $dueDate->format('M d, Y'),
            route('student.my-books')
        );
        
        return response()->json(['success' => true, 'borrow' => $borrow->load(['user', 'book'])]);
    }

    public function staffReturnBook(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'staff') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $borrow = Borrow::findOrFail($id);
        $borrow->update([
            'status' => 'returned',
            'return_date' => now(),
        ]);
        
        $borrow->book->increment('available_copies');
        
        // Calculate fine if overdue
        if ($borrow->due_date < now()) {
            $daysOverdue = now()->diffInDays($borrow->due_date);
            // Use the fine_per_day stored at issue time, or calculate based on issue duration
            $finePerDay = $borrow->fine_per_day ?? \App\Helpers\FineHelper::getFinePerDayByDuration($borrow->issue_duration_days ?? 15);
            $fineAmount = $daysOverdue * $finePerDay;
            
            Fine::create([
                'user_id' => $borrow->user_id,
                'borrow_id' => $borrow->id,
                'amount' => $fineAmount,
                'status' => 'pending',
                'reason' => "Overdue return - {$daysOverdue} day(s) late",
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
            'You have successfully returned "' . $borrow->book->title . '".',
            route('student.my-books')
        );
        
        return response()->json(['success' => true, 'borrow' => $borrow->load(['user', 'book'])]);
    }

    public function staffExtendBorrow(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'staff') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'additional_days' => 'required|integer|min:1|max:30',
        ]);
        
        $borrow = Borrow::findOrFail($id);
        if ($borrow->status === 'returned') {
            return response()->json(['success' => false, 'message' => 'Cannot extend due date for returned book'], 400);
        }
        
        $oldDueDate = $borrow->due_date->copy();
        $borrow->extendDueDate($request->additional_days);
        
        // Notify student
        \App\Helpers\NotificationHelper::createNotification(
            $borrow->user_id,
            'book_due_date_extended',
            'Due Date Extended',
            'The due date for "' . $borrow->book->title . '" has been extended from ' . $oldDueDate->format('M d, Y') . ' to ' . $borrow->due_date->format('M d, Y'),
            route('student.my-books')
        );
        
        return response()->json(['success' => true, 'borrow' => $borrow->load(['user', 'book'])]);
    }

    public function staffFines(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'staff') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $fines = Fine::with(['user', 'borrow.book'])->latest()->get();
        return response()->json(['success' => true, 'fines' => $fines]);
    }

    public function staffUpdateFineStatus(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'staff') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate(['status' => 'required|in:pending,paid']);
        
        $fine = Fine::findOrFail($id);
        $fine->update(['status' => $request->status]);
        
        return response()->json(['success' => true, 'fine' => $fine]);
    }

    public function staffBookRequests(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'staff') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $requests = BookRequest::with(['user', 'book.author', 'book.category'])
            ->latest()
            ->get();
        return response()->json(['success' => true, 'requests' => $requests]);
    }

    public function staffLibraryCards(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'staff') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $cards = LibraryCard::with('user')->latest()->get();
        return response()->json(['success' => true, 'cards' => $cards]);
    }

    // ============ STUDENT MODULES ============

    public function studentMyBooks(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'student') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $borrows = Borrow::where('user_id', $user->id)
            ->with(['book.author', 'book.category'])
            ->latest()
            ->get();
        return response()->json(['success' => true, 'borrows' => $borrows]);
    }

    public function studentRequestBook(Request $request, $bookId)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'student') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $book = Book::findOrFail($bookId);
        
        // Check if already requested
        $existingRequest = BookRequest::where('user_id', $user->id)
            ->where('book_id', $bookId)
            ->where('status', 'pending')
            ->first();
        
        if ($existingRequest) {
            return response()->json(['success' => false, 'message' => 'Already requested'], 400);
        }
        
        $bookRequest = BookRequest::create([
            'user_id' => $user->id,
            'book_id' => $bookId,
            'status' => 'pending',
        ]);
        
        return response()->json(['success' => true, 'request' => $bookRequest->load('book')]);
    }

    public function studentFines(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'student') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $fines = Fine::where('user_id', $user->id)
            ->with('borrow.book')
            ->latest()
            ->get();
        return response()->json(['success' => true, 'fines' => $fines]);
    }

    public function studentLibraryCard(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'student') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $card = LibraryCard::where('user_id', $user->id)->latest()->first();
        return response()->json(['success' => true, 'card' => $card]);
    }

    public function studentReservations(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user || $user->role !== 'student') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $reservations = BookReservation::where('user_id', $user->id)
            ->with(['book.author', 'book.category'])
            ->latest()
            ->get();
        return response()->json(['success' => true, 'reservations' => $reservations]);
    }

    // ============ SHARED MODULES ============

    // Chat
    public function getChatUsers(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $chattableUsers = [];
        
        if ($user->role === 'admin') {
            $chattableUsers = User::where('role', 'staff')->get();
        } elseif ($user->role === 'staff') {
            $chattableUsers = User::whereIn('role', ['admin', 'student'])->get();
        } elseif ($user->role === 'student') {
            $chattableUsers = User::where('role', 'staff')->get();
        }
        
        return response()->json(['success' => true, 'users' => $chattableUsers]);
    }

    public function getConversations(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $conversations = Conversation::where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->with(['userOne', 'userTwo', 'latestMessage.sender'])
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($conversation) use ($user) {
                $otherUser = $conversation->getOtherUser($user->id);
                $unreadCount = $conversation->unreadMessagesCount($user->id);
                
                return [
                    'id' => $conversation->id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'email' => $otherUser->email,
                        'role' => $otherUser->role,
                    ],
                    'last_message' => $conversation->latestMessage ? [
                        'message' => $conversation->latestMessage->message,
                        'sender_id' => $conversation->latestMessage->sender_id,
                        'created_at' => $conversation->latestMessage->created_at,
                    ] : null,
                    'unread_count' => $unreadCount,
                    'last_message_at' => $conversation->last_message_at,
                ];
            });
        
        return response()->json(['success' => true, 'conversations' => $conversations]);
    }

    public function getMessages(Request $request, $conversationId)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $messages = Message::where('conversation_id', $conversationId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Mark as delivered and read
        Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $user->id)
            ->where('delivery_status', 'sent')
            ->update([
                'delivery_status' => 'delivered',
                'delivered_at' => now(),
            ]);
        
        Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->each(function ($message) {
                $message->markAsRead();
            });
        
        return response()->json(['success' => true, 'messages' => $messages]);
    }

    public function sendMessage(Request $request, $conversationId)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate(['message' => 'required|string|max:5000']);
        
        $conversation = Conversation::findOrFail($conversationId);
        
        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $user->id,
            'message' => $request->message,
            'delivery_status' => 'sent',
        ]);
        
        $conversation->update(['last_message_at' => now()]);
        
        event(new \App\Events\MessageSent($message->load('sender')));
        
        return response()->json(['success' => true, 'message' => $message->load('sender')]);
    }

    public function createConversation(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate(['user_id' => 'required|exists:users,id']);
        
        $otherUser = User::findOrFail($request->user_id);
        
        // Check if can chat
        $canChat = false;
        if ($user->role === 'admin' && $otherUser->role === 'staff') {
            $canChat = true;
        } elseif ($user->role === 'staff' && in_array($otherUser->role, ['admin', 'student'])) {
            $canChat = true;
        } elseif ($user->role === 'student' && $otherUser->role === 'staff') {
            $canChat = true;
        }
        
        if (!$canChat) {
            return response()->json(['success' => false, 'message' => 'Cannot chat with this user'], 403);
        }
        
        $conversation = Conversation::getOrCreate($user->id, $otherUser->id);
        
        return response()->json(['success' => true, 'conversation' => $conversation->load(['userOne', 'userTwo'])]);
    }

    // Notifications
    public function getNotifications(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($notif) use ($user) {
                // Fix link based on user role
                $link = $notif->link;
                if ($link) {
                    $link = \App\Helpers\NotificationHelper::getRoleBasedLink($user, $link);
                }
                
                return [
                    'id' => $notif->id,
                    'type' => $notif->type,
                    'title' => $notif->title,
                    'message' => $notif->message,
                    'link' => $link,
                    'is_read' => $notif->is_read,
                    'created_at' => $notif->created_at->toISOString(),
                ];
            });
        
        return response()->json(['success' => true, 'notifications' => $notifications]);
    }

    public function markNotificationRead(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $notification = Notification::where('user_id', $user->id)->findOrFail($id);
        $notification->update(['is_read' => true]);
        
        return response()->json(['success' => true]);
    }

    // Profile
    public function updateProfile(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'father_name' => 'sometimes|string|max:255',
            'mother_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:500',
            'date_of_birth' => 'sometimes|date',
        ]);
        
        $user->update($request->only(['name', 'father_name', 'mother_name', 'phone', 'address', 'date_of_birth']));
        
        return response()->json(['success' => true, 'user' => $user]);
    }

    public function changePassword(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        if (!\Hash::check($request->current_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect'], 400);
        }
        
        $user->update(['password' => \Hash::make($request->password)]);
        
        return response()->json(['success' => true, 'message' => 'Password updated successfully']);
    }
}
