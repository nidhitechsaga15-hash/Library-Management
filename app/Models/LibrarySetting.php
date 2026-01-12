<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibrarySetting extends Model
{
    protected $fillable = [
        'library_id',
        'staff_id',
        'book_issue_duration_days',
        'book_collection_deadline_days',
        'max_books_per_student',
        'max_books_per_subject',
        'fine_per_day',
        'almirah_config',
    ];

    protected function casts(): array
    {
        return [
            'book_issue_duration_days' => 'integer',
            'book_collection_deadline_days' => 'integer',
            'max_books_per_student' => 'integer',
            'max_books_per_subject' => 'integer',
            'fine_per_day' => 'decimal:2',
            'almirah_config' => 'array',
        ];
    }

    public function library(): BelongsTo
    {
        return $this->belongsTo(Library::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
