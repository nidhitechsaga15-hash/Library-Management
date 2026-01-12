<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'isbn',
        'title',
        'description',
        'author_id',
        'category_id',
        'publisher_id',
        'library_id',
        'course',
        'semester',
        'year',
        'batch',
        'subject',
        'publisher',
        'edition',
        'publication_year',
        'total_copies',
        'available_copies',
        'rack_number',
        'almirah',
        'row',
        'book_serial',
        'qr_code',
        'barcode',
        'language',
        'pages',
        'cover_image',
        'status',
        'condition_status',
        'condition_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'publication_year' => 'integer',
            'total_copies' => 'integer',
            'available_copies' => 'integer',
            'pages' => 'integer',
            'condition_updated_at' => 'date',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function library(): BelongsTo
    {
        return $this->belongsTo(Library::class);
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(BookCondition::class);
    }

    public function borrows(): HasMany
    {
        return $this->hasMany(Borrow::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(BookReservation::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(BookRequest::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->available_copies > 0;
    }

    public function hasAvailableCopies(): bool
    {
        return $this->available_copies > 0;
    }

    /**
     * Get effective available copies (accounting for hold requests)
     * Hold requests don't deduct stock until approved, so we add back any incorrectly deducted stock
     */
    public function getEffectiveAvailableCopiesAttribute(): int
    {
        // Count hold requests that have stock_deducted = true (old data issue)
        // These should not have deducted stock, so we add them back
        $incorrectlyDeductedHolds = $this->requests()
            ->where('status', 'hold')
            ->where('stock_deducted', true)
            ->count();

        // Add back stock for hold requests that incorrectly deducted stock
        return $this->available_copies + $incorrectlyDeductedHolds;
    }

    /**
     * Get full location string
     */
    public function getFullLocationAttribute(): string
    {
        $location = [];
        if ($this->almirah) {
            $location[] = 'Almirah ' . $this->almirah;
        }
        if ($this->row) {
            $location[] = 'Row ' . $this->row;
        }
        if ($this->book_serial) {
            $location[] = 'Serial ' . $this->book_serial;
        }
        return !empty($location) ? implode(' → ', $location) : 'Location not set';
    }

    /**
     * Get almirah number (alias for almirah)
     */
    public function getAlmirahNoAttribute(): ?string
    {
        return $this->almirah;
    }

    /**
     * Get row number (alias for row)
     */
    public function getRowNoAttribute(): ?string
    {
        return $this->row;
    }
}
