<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'name',
        'date',
        'is_recurring',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_recurring' => 'boolean',
        ];
    }

    /**
     * Check if a date is a holiday
     */
    public static function isHoliday($date)
    {
        $dateStr = $date instanceof \Carbon\Carbon ? $date->format('Y-m-d') : $date;
        $year = date('Y', strtotime($dateStr));
        $month = date('m', strtotime($dateStr));
        $day = date('d', strtotime($dateStr));

        // Check exact date
        $exact = self::whereDate('date', $dateStr)->first();
        if ($exact) {
            return true;
        }

        // Check recurring holidays (same month and day, different year)
        $recurring = self::where('is_recurring', true)
            ->whereMonth('date', $month)
            ->whereDay('date', $day)
            ->first();

        return $recurring !== null;
    }

    /**
     * Get all holidays for a date range
     */
    public static function getHolidaysInRange($startDate, $endDate)
    {
        return self::whereBetween('date', [$startDate, $endDate])
            ->orWhere(function ($query) use ($startDate, $endDate) {
                $query->where('is_recurring', true)
                    ->whereMonth('date', '>=', date('m', strtotime($startDate)))
                    ->whereMonth('date', '<=', date('m', strtotime($endDate)));
            })
            ->get();
    }
}
