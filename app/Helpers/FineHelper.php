<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class FineHelper
{
    /**
     * Get fine per day based on issue duration
     * 
     * @param int $issueDurationDays The issue duration in days
     * @return float Fine per day amount
     */
    public static function getFinePerDayByDuration(int $issueDurationDays): float
    {
        // Try to get fine mapping from settings
        $fineMapping = self::getFineMappingFromSettings();
        
        if ($fineMapping) {
            // Find the appropriate fine rate based on issue duration
            // Sort by duration descending to match longest duration first
            krsort($fineMapping);
            
            foreach ($fineMapping as $duration => $finePerDay) {
                if ($issueDurationDays <= $duration) {
                    return (float) $finePerDay;
                }
            }
            
            // If no match found, return the highest fine rate (for longest duration)
            return (float) end($fineMapping);
        }
        
        // Default mapping if settings not configured
        return self::getDefaultFinePerDay($issueDurationDays);
    }
    
    /**
     * Get fine mapping from settings table
     * 
     * @return array|null Fine mapping array [duration => fine_per_day] or null
     */
    private static function getFineMappingFromSettings(): ?array
    {
        $setting = DB::table('settings')->where('key', 'fine_duration_mapping')->first();
        
        if ($setting && $setting->value) {
            $mapping = json_decode($setting->value, true);
            if (is_array($mapping)) {
                return $mapping;
            }
        }
        
        return null;
    }
    
    /**
     * Get default fine per day based on issue duration
     * Default mapping:
     * - 15 days or less → ₹10 per day
     * - 16-30 days → ₹20 per day
     * - 31-60 days → ₹35 per day
     * - 61+ days → ₹50 per day
     * 
     * @param int $issueDurationDays
     * @return float
     */
    private static function getDefaultFinePerDay(int $issueDurationDays): float
    {
        if ($issueDurationDays <= 15) {
            return 10.00;
        } elseif ($issueDurationDays <= 30) {
            return 20.00;
        } elseif ($issueDurationDays <= 60) {
            return 35.00;
        } else {
            return 50.00;
        }
    }
    
    /**
     * Save fine mapping to settings
     * 
     * @param array $mapping Fine mapping array [duration => fine_per_day]
     * @return void
     */
    public static function saveFineMapping(array $mapping): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'fine_duration_mapping'],
            [
                'value' => json_encode($mapping),
                'type' => 'json',
                'description' => 'Fine per day mapping based on issue duration (days => fine_per_day)',
                'updated_at' => now(),
            ]
        );
    }
    
    /**
     * Get fine mapping for display/editing
     * 
     * @return array Fine mapping array
     */
    public static function getFineMapping(): array
    {
        $mapping = self::getFineMappingFromSettings();
        
        if ($mapping) {
            return $mapping;
        }
        
        // Return default mapping
        return [
            15 => 10.00,
            30 => 20.00,
            60 => 35.00,
            365 => 50.00,
        ];
    }
}

