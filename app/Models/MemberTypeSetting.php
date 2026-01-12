<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberTypeSetting extends Model
{
    protected $fillable = [
        'member_type',
        'max_books_allowed',
        'issue_duration_days',
        'fine_per_day',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'max_books_allowed' => 'integer',
            'issue_duration_days' => 'integer',
            'fine_per_day' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get setting for a specific member type
     */
    public static function getForMemberType($memberType)
    {
        return self::where('member_type', $memberType)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get max books allowed for member type
     */
    public static function getMaxBooks($memberType)
    {
        $setting = self::getForMemberType($memberType);
        return $setting ? $setting->max_books_allowed : 2; // Default to 2
    }

    /**
     * Get issue duration for member type
     */
    public static function getIssueDuration($memberType)
    {
        $setting = self::getForMemberType($memberType);
        return $setting ? $setting->issue_duration_days : 14; // Default to 14 days
    }
}
