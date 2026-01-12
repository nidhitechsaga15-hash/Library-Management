<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookCondition extends Model
{
    protected $fillable = [
        'book_id',
        'reported_by',
        'condition_type',
        'description',
        'notes',
        'status',
        'reported_date',
        'resolved_date',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'reported_date' => 'date',
            'resolved_date' => 'date',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
