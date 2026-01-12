<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Borrow extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'borrow_date',
        'issue_duration_days',
        'due_date',
        'fine_per_day',
        'last_fine_paid_date',
        'total_fine_paid',
        'return_date',
        'status',
        'notes',
        'issued_by',
    ];

    protected function casts(): array
    {
        return [
            'borrow_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
            'last_fine_paid_date' => 'date',
            'fine_per_day' => 'decimal:2',
            'total_fine_paid' => 'decimal:2',
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

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function fine(): HasOne
    {
        return $this->hasOne(Fine::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'borrowed' && $this->due_date < now() && $this->return_date === null;
    }

    /**
     * Calculate days left until due date
     */
    public function getDaysLeftAttribute(): int
    {
        if ($this->status === 'returned') {
            return 0;
        }
        
        $daysLeft = now()->diffInDays($this->due_date, false);
        return max(0, $daysLeft);
    }

    /**
     * Calculate days overdue (total days from due date)
     */
    public function getDaysOverdueAttribute(): int
    {
        if ($this->status === 'returned' || $this->due_date >= now()) {
            return 0;
        }
        
        // Calculate days overdue (always positive)
        // When due_date is in the past, diffInDays returns negative, so we use abs
        $daysOverdue = abs(now()->diffInDays($this->due_date, false));
        return (int) $daysOverdue;
    }

    /**
     * Get fine start date (from last paid date or due date)
     */
    public function getFineStartDateAttribute()
    {
        if (!$this->isOverdue()) {
            return null;
        }
        
        // If last_fine_paid_date exists, fine starts from next day
        if ($this->last_fine_paid_date) {
            return \Carbon\Carbon::parse($this->last_fine_paid_date)->addDay();
        }
        
        // Otherwise, fine starts from due_date + 1 day
        return \Carbon\Carbon::parse($this->due_date)->addDay();
    }

    /**
     * Calculate pending fine days (from last paid date to today)
     * Formula: fine_start_date = last_fine_paid_date + 1 day (or due_date + 1 if no payment)
     *          pending_days = today - fine_start_date + 1 (inclusive)
     */
    public function getPendingFineDaysAttribute(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        $today = now()->startOfDay();
        
        // Determine fine start date
        if ($this->last_fine_paid_date) {
            // Fine starts from the day after last paid date
            $fineStartDate = \Carbon\Carbon::parse($this->last_fine_paid_date)->startOfDay()->addDay();
        } else {
            // Fine starts from the day after due date
            $fineStartDate = \Carbon\Carbon::parse($this->due_date)->startOfDay()->addDay();
        }
        
        // If start date is in future, return 0
        if ($fineStartDate > $today) {
            return 0;
        }
        
        // Calculate days from fine_start_date to today (inclusive)
        // Use abs() to ensure positive value, and add 1 to make it inclusive
        $daysDiff = abs($today->diffInDays($fineStartDate, false));
        
        // Add 1 to make it inclusive (both start and end day count)
        $pendingDays = $daysDiff + 1;
        
        return max(0, (int) $pendingDays);
    }

    /**
     * Calculate current pending fine amount (only for unpaid days)
     */
    public function getCurrentFineAmountAttribute(): float
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        $pendingDays = $this->pending_fine_days;
        if ($pendingDays <= 0) {
            return 0;
        }
        
        // Use the fine_per_day stored at issue time, or calculate based on issue duration
        $finePerDay = $this->fine_per_day ?? \App\Helpers\FineHelper::getFinePerDayByDuration($this->issue_duration_days ?? 15);
        
        return $pendingDays * $finePerDay;
    }

    /**
     * Get total fine amount (including paid)
     */
    public function getTotalFineAmountAttribute(): float
    {
        if (!$this->isOverdue()) {
            return $this->total_fine_paid ?? 0;
        }
        
        $totalDaysOverdue = $this->days_overdue;
        $finePerDay = $this->fine_per_day ?? \App\Helpers\FineHelper::getFinePerDayByDuration($this->issue_duration_days ?? 15);
        
        return $totalDaysOverdue * $finePerDay;
    }

    /**
     * Check if due tomorrow (for reminder)
     */
    public function isDueTomorrow(): bool
    {
        return $this->status === 'borrowed' 
            && $this->due_date->isTomorrow() 
            && $this->return_date === null;
    }

    /**
     * Check if due today
     */
    public function isDueToday(): bool
    {
        return $this->status === 'borrowed' 
            && $this->due_date->isToday() 
            && $this->return_date === null;
    }

    /**
     * Extend due date by additional days
     */
    public function extendDueDate(int $additionalDays): void
    {
        $newDueDate = $this->due_date->copy()->addDays($additionalDays);
        $this->update([
            'due_date' => $newDueDate,
            'issue_duration_days' => ($this->issue_duration_days ?? 0) + $additionalDays,
        ]);
    }
}
