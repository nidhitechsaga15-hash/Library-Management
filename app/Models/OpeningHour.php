<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpeningHour extends Model
{
    protected $fillable = [
        'day_of_week',
        'opening_time',
        'closing_time',
        'is_closed',
    ];

    protected function casts(): array
    {
        return [
            'is_closed' => 'boolean',
        ];
    }

    /**
     * Get opening time as Carbon instance
     */
    public function getOpeningTimeAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value) : null;
    }

    /**
     * Get closing time as Carbon instance
     */
    public function getClosingTimeAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value) : null;
    }

    /**
     * Check if library is open on a specific day
     */
    public function isOpen()
    {
        return !$this->is_closed && $this->opening_time && $this->closing_time;
    }

    /**
     * Get formatted time range
     */
    public function getTimeRangeAttribute()
    {
        if ($this->is_closed) {
            return 'Closed';
        }
        $opening = $this->attributes['opening_time'] ?? null;
        $closing = $this->attributes['closing_time'] ?? null;
        if (!$opening || !$closing) {
            return 'Not Set';
        }
        return date('H:i', strtotime($opening)) . ' - ' . date('H:i', strtotime($closing));
    }
}
