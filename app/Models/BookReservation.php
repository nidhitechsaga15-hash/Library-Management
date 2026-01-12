<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookReservation extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'status',
        'reserved_at',
        'notified_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'reserved_at' => 'datetime',
            'notified_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
