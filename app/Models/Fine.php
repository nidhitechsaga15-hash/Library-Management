<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fine extends Model
{
    protected $fillable = [
        'borrow_id',
        'user_id',
        'amount',
        'paid_amount',
        'remaining_amount',
        'reason',
        'status',
        'paid_date',
        'payment_notes',
        'days_paid',
        'days_overdue_at_creation',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'paid_date' => 'date',
            'days_paid' => 'integer',
            'days_overdue_at_creation' => 'integer',
        ];
    }

    /**
     * Get remaining amount to be paid
     * Recalculates based on last_fine_paid_date (only unpaid days)
     */
    public function getRemainingAmountAttribute()
    {
        // If fine is fully paid, return 0
        if ($this->status === 'paid' && $this->isFullyPaid()) {
            return 0;
        }
        
        // If book is still overdue, calculate from last_fine_paid_date
        if ($this->borrow && $this->borrow->isOverdue()) {
            return $this->calculateRemainingFine();
        }
        
        // Otherwise, return the difference between amount and paid amount
        if (!$this->paid_amount) {
            return $this->amount;
        }
        return max(0, $this->amount - $this->paid_amount);
    }
    
    /**
     * Calculate remaining fine based on current overdue days
     * Uses last_fine_paid_date to calculate only unpaid days
     */
    public function calculateRemainingFine(): float
    {
        if (!$this->borrow || !$this->borrow->isOverdue()) {
            return 0;
        }
        
        // Use Borrow model's current_fine_amount which calculates from last_fine_paid_date
        return $this->borrow->current_fine_amount;
    }

    /**
     * Check if fine is fully paid
     * For overdue books, checks if all current overdue days are paid
     */
    public function isFullyPaid()
    {
        // If book is still overdue, check if remaining fine is 0
        if ($this->borrow && $this->borrow->isOverdue()) {
            $remaining = $this->calculateRemainingFine();
            return $remaining <= 0.01; // Allow small floating point differences
        }
        
        // Otherwise, check if paid amount covers the fine amount
        return ($this->paid_amount ?? 0) >= $this->amount;
    }

    /**
     * Record partial payment
     * Updates last_fine_paid_date and total_fine_paid in borrow table
     */
    public function recordPayment($amount, $notes = null)
    {
        $oldPaidAmount = $this->paid_amount ?? 0;
        $this->paid_amount = $oldPaidAmount + $amount;
        
        // Update borrow table's last_fine_paid_date and total_fine_paid
        if ($this->borrow) {
            // Calculate how many days this payment covers
            $finePerDay = $this->borrow->fine_per_day ?? \App\Helpers\FineHelper::getFinePerDayByDuration($this->borrow->issue_duration_days ?? 15);
            
            if ($finePerDay > 0) {
                $daysPaidByThisPayment = floor($amount / $finePerDay);
                
                // Update last_fine_paid_date
                // If no last_fine_paid_date, start from due_date
                if (!$this->borrow->last_fine_paid_date) {
                    $this->borrow->last_fine_paid_date = $this->borrow->due_date;
                }
                
                // Add paid days to last_fine_paid_date
                // This represents the last date for which fine has been paid
                $currentLastPaidDate = \Carbon\Carbon::parse($this->borrow->last_fine_paid_date);
                $this->borrow->last_fine_paid_date = $currentLastPaidDate->copy()->addDays($daysPaidByThisPayment);
                
                // Update total_fine_paid
                $this->borrow->total_fine_paid = ($this->borrow->total_fine_paid ?? 0) + $amount;
                
                $this->borrow->save();
                
                $this->days_paid = ($this->days_paid ?? 0) + $daysPaidByThisPayment;
            }
        }
        
        // Recalculate remaining amount based on current overdue status
        if ($this->borrow && $this->borrow->isOverdue()) {
            $this->remaining_amount = $this->calculateRemainingFine();
            // Update total amount to reflect current pending fine
            $this->amount = $this->borrow->current_fine_amount;
            $pendingDays = $this->borrow->pending_fine_days;
            $this->reason = 'Overdue book - ' . $pendingDays . ' day(s) pending from ' . 
                ($this->borrow->last_fine_paid_date ? $this->borrow->last_fine_paid_date->format('Y-m-d') : $this->borrow->due_date->format('Y-m-d'));
        } else {
            $this->remaining_amount = max(0, $this->amount - $this->paid_amount);
        }
        
        if ($notes) {
            $this->payment_notes = ($this->payment_notes ? $this->payment_notes . "\n" : '') . date('Y-m-d H:i:s') . ': ' . $notes;
        }
        
        // Check if fine is fully paid (all current pending days are paid)
        if ($this->isFullyPaid()) {
            $this->status = 'paid';
            $this->paid_date = now();
        } else {
            // If still overdue, keep status as pending
            $this->status = 'pending';
        }
        
        $this->save();
    }

    public function borrow(): BelongsTo
    {
        return $this->belongsTo(Borrow::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
