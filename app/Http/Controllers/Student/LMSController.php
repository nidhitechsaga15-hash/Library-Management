<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LMSCourse;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LMSController extends Controller
{
    /**
     * Get course-specific book recommendations
     */
    public function getRecommendations()
    {
        $user = Auth::user();
        
        // Get recommendations based on LMS course data
        $recommendedBooks = LMSCourse::getCourseRecommendations($user);
        
        // Also get books matching user's course details
        $courseBooks = LMSCourse::getBooksForUser($user);
        
        // Merge and remove duplicates
        $allBooks = $recommendedBooks->merge($courseBooks)->unique('id');
        
        return response()->json([
            'success' => true,
            'books' => $allBooks->map(function($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author->name ?? 'Unknown',
                    'category' => $book->category->name ?? 'Uncategorized',
                    'isbn' => $book->isbn,
                    'available_copies' => $book->available_copies,
                    'course' => $book->course,
                    'semester' => $book->semester,
                    'year' => $book->year,
                ];
            }),
            'user_course' => [
                'course' => $user->course,
                'semester' => $user->semester,
                'year' => $user->year,
                'batch' => $user->batch,
            ],
        ]);
    }

    /**
     * Get books for specific course
     */
    public function getCourseBooks(Request $request)
    {
        $validated = $request->validate([
            'course' => 'nullable|string',
            'semester' => 'nullable|integer',
            'year' => 'nullable|string',
            'batch' => 'nullable|string',
        ]);

        $user = Auth::user();
        
        $query = Book::with(['author', 'category'])
            ->where('status', 'available')
            ->where('available_copies', '>', 0);

        if ($validated['course'] ?? $user->course) {
            $query->where('course', $validated['course'] ?? $user->course);
        }

        if (isset($validated['semester']) || $user->semester) {
            $query->where('semester', $validated['semester'] ?? $user->semester);
        }

        if ($validated['year'] ?? $user->year) {
            $query->where('year', $validated['year'] ?? $user->year);
        }

        if ($validated['batch'] ?? $user->batch) {
            $query->where('batch', $validated['batch'] ?? $user->batch);
        }

        $books = $query->latest()->get();

        return response()->json([
            'success' => true,
            'books' => $books->map(function($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author->name ?? 'Unknown',
                    'category' => $book->category->name ?? 'Uncategorized',
                    'isbn' => $book->isbn,
                    'available_copies' => $book->available_copies,
                ];
            }),
        ]);
    }

    /**
     * Show LMS recommendations page
     */
    public function index()
    {
        $user = Auth::user();
        $recommendedBooks = LMSCourse::getCourseRecommendations($user);
        $courseBooks = LMSCourse::getBooksForUser($user);
        $allBooks = $recommendedBooks->merge($courseBooks)->unique('id');

        return view('student.lms.recommendations', compact('allBooks', 'user'));
    }
}
