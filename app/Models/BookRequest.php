<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookRequest extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'status',
        'notes',
        'approved_by',
        'approved_at',
        'collection_deadline',
        'hold_expires_at',
        'received_at',
        'stock_deducted',
        'stock_deducted_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'collection_deadline' => 'datetime',
            'hold_expires_at' => 'datetime',
            'received_at' => 'datetime',
            'stock_deducted' => 'boolean',
            'stock_deducted_at' => 'datetime',
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

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if request is on hold
     */
    public function isOnHold(): bool
    {
        return $this->status === 'hold';
    }

    /**
     * Check if hold has expired
     */
    public function isHoldExpired(): bool
    {
        return $this->hold_expires_at && $this->hold_expires_at->isPast() && $this->status === 'hold';
    }

    /**
     * Check if request is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
