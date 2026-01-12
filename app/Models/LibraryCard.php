<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class LibraryCard extends Model
{
    protected $fillable = [
        'user_id',
        'card_number',
        'issue_date',
        'validity_date',
        'status',
        'qr_code',
        'issued_by',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'validity_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->validity_date >= now();
    }

    public function isExpired()
    {
        return $this->validity_date < now();
    }

    public function isBlocked()
    {
        return $this->status === 'blocked' || $this->status === 'lost';
    }

    public function isValid()
    {
        return $this->isActive() && !$this->isBlocked();
    }

    public function markAsBlocked($reason = 'Lost card')
    {
        $this->update([
            'status' => 'blocked',
            'notes' => $reason,
        ]);
    }

    public function markAsLost()
    {
        $this->update([
            'status' => 'lost',
        ]);
    }

    public function renew($newValidityDate)
    {
        $this->update([
            'validity_date' => $newValidityDate,
            'status' => 'active',
        ]);
    }

    public static function generateCardNumber()
    {
        $prefix = 'LIB';
        $year = date('Y');
        $lastCard = self::where('card_number', 'like', $prefix . $year . '%')
            ->orderBy('card_number', 'desc')
            ->first();
        
        if ($lastCard) {
            $lastNumber = (int) substr($lastCard->card_number, -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
