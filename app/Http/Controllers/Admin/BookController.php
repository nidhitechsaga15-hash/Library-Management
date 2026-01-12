<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Author;
use App\Models\Category;
use App\Models\Library;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with(['author', 'category']);

        // Search functionality
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

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by author
        if ($request->filled('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        // Filter by library
        if ($request->filled('library_id')) {
            $query->where('library_id', $request->library_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by publisher
        if ($request->filled('publisher')) {
            $query->where('publisher', 'like', '%' . $request->publisher . '%');
        }

        // Filter by publication year range
        if ($request->filled('year_from')) {
            $query->where('publication_year', '>=', $request->year_from);
        }
        if ($request->filled('year_to')) {
            $query->where('publication_year', '<=', $request->year_to);
        }

        // Filter by available copies (low stock)
        if ($request->filled('available_copies_filter')) {
            if ($request->available_copies_filter === 'low_stock') {
                $query->where('available_copies', '<=', 5)->where('available_copies', '>', 0);
            } elseif ($request->available_copies_filter === 'out_of_stock') {
                $query->where('available_copies', 0);
            } elseif ($request->available_copies_filter === 'in_stock') {
                $query->where('available_copies', '>', 5);
            }
        }

        // Filter by language
        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        $books = $query->latest()->paginate(100)->withQueryString();
        
        $authors = Author::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $libraries = Library::where('is_active', true)->orderBy('name')->get();
        
        // Get unique publishers for filter
        $publishers = Book::whereNotNull('publisher')
            ->distinct()
            ->orderBy('publisher')
            ->pluck('publisher')
            ->filter();
        
        // Get unique languages for filter
        $languages = Book::whereNotNull('language')
            ->distinct()
            ->orderBy('language')
            ->pluck('language')
            ->filter();
        
        return view('admin.books.index', compact('books', 'authors', 'categories', 'libraries', 'publishers', 'languages'));
    }

    public function create()
    {
        $authors = Author::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $libraries = Library::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.books.create', compact('authors', 'categories', 'libraries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'isbn' => 'required|string|unique:books,isbn',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'author_id' => 'required|exists:authors,id',
            'category_id' => 'required|exists:categories,id',
            'library_id' => 'nullable|exists:libraries,id',
            'publisher' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:50',
            'publication_year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'available_copies' => 'required|integer|min:0',
            'rack_number' => 'nullable|string|max:50',
            'almirah' => 'nullable|string|max:50',
            'row' => 'nullable|string|max:50',
            'book_serial' => 'nullable|string|max:50',
            'qr_code' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:50',
            'pages' => 'nullable|integer|min:1',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:available,unavailable',
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('book-covers', 'public');
        }

        // Set total_copies equal to available_copies
        $validated['total_copies'] = $validated['available_copies'];

        $book = Book::create($validated);

        // Notify all students about new book (optional - can be disabled for bulk imports)
        if ($request->has('notify_students')) {
            $students = \App\Models\User::where('role', 'student')->where('is_active', true)->get();
            foreach ($students as $student) {
                $student->notify(new \App\Notifications\NewBookArrival($book));
            }
        }

        return redirect()->route('admin.books.index')
            ->with('success', 'Book created successfully!');
    }

    public function show(Book $book)
    {
        $book->load(['author', 'category', 'borrows.user']);
        
        return view('admin.books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $authors = Author::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $libraries = Library::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.books.edit', compact('book', 'authors', 'categories', 'libraries'));
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'isbn' => 'required|string|unique:books,isbn,' . $book->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'author_id' => 'required|exists:authors,id',
            'category_id' => 'required|exists:categories,id',
            'library_id' => 'nullable|exists:libraries,id',
            'publisher' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:50',
            'publication_year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'available_copies' => 'required|integer|min:0',
            'rack_number' => 'nullable|string|max:50',
            'almirah' => 'nullable|string|max:50',
            'row' => 'nullable|string|max:50',
            'book_serial' => 'nullable|string|max:50',
            'qr_code' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:50',
            'pages' => 'nullable|integer|min:1',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:available,unavailable',
        ]);

        if ($request->hasFile('cover_image')) {
            // Delete old image if exists
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('book-covers', 'public');
        }

        // Set total_copies equal to available_copies
        $validated['total_copies'] = $validated['available_copies'];

        $book->update($validated);

        return redirect()->route('admin.books.index')
            ->with('success', 'Book updated successfully!');
    }

    public function destroy(Book $book)
    {
        if ($book->borrows()->where('status', 'borrowed')->exists()) {
            return redirect()->route('admin.books.index')
                ->with('error', 'Cannot delete book with active borrows!');
        }

        $book->delete();

        return redirect()->route('admin.books.index')
            ->with('success', 'Book deleted successfully!');
    }
}
