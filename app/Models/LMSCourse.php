<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LMSCourse extends Model
{
    protected $table = 'lms_courses';
    
    protected $fillable = [
        'course_code',
        'course_name',
        'description',
        'department',
        'semester',
        'year',
        'batch',
        'subjects',
        'recommended_books',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'subjects' => 'array',
            'recommended_books' => 'array',
            'semester' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get recommended books for this course
     */
    public function getRecommendedBooks()
    {
        if (!$this->recommended_books) {
            return collect();
        }

        return \App\Models\Book::whereIn('id', $this->recommended_books)
            ->where('status', 'available')
            ->with(['author', 'category'])
            ->get();
    }

    /**
     * Get books by course matching user's course details
     */
    public static function getBooksForUser($user)
    {
        $query = \App\Models\Book::with(['author', 'category'])
            ->where('status', 'available')
            ->where('available_copies', '>', 0);

        // Match by course
        if ($user->course) {
            $query->where('course', $user->course);
        }

        // Match by semester
        if ($user->semester) {
            $query->where('semester', $user->semester);
        }

        // Match by year
        if ($user->year) {
            $query->where('year', $user->year);
        }

        // Match by batch
        if ($user->batch) {
            $query->where('batch', $user->batch);
        }

        return $query->latest()->get();
    }

    /**
     * Get course-specific recommendations based on LMS data
     */
    public static function getCourseRecommendations($user)
    {
        // First try to find matching LMS course
        $lmsCourse = self::where('is_active', true)
            ->where(function($q) use ($user) {
                if ($user->course) {
                    $q->where('course_code', 'like', '%' . $user->course . '%')
                      ->orWhere('course_name', 'like', '%' . $user->course . '%');
                }
                if ($user->semester) {
                    $q->orWhere('semester', $user->semester);
                }
                if ($user->department) {
                    $q->orWhere('department', $user->department);
                }
            })
            ->first();

        if ($lmsCourse && $lmsCourse->recommended_books) {
            return $lmsCourse->getRecommendedBooks();
        }

        // Fallback to regular course-based matching
        return self::getBooksForUser($user);
    }
}
