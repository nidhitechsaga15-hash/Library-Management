<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\User;

class NotificationHelper
{
    public static function createNotification($userId, $type, $title, $message, $link = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => false,
        ]);
    }

    public static function notifyAdminsAndStaff($type, $title, $message, $link = null)
    {
        $users = User::whereIn('role', ['admin', 'staff'])->get();
        
        foreach ($users as $user) {
            // Generate role-based link
            $roleBasedLink = self::getRoleBasedLink($user, $link);
            self::createNotification($user->id, $type, $title, $message, $roleBasedLink);
        }
    }

    public static function getRoleBasedLink($user, $link)
    {
        if (!$link) {
            return null;
        }

        // Convert full URL to relative path if needed
        $link = self::convertToRelativePath($link);

        // Chat links are the same for all roles
        if (strpos($link, '/chat') !== false) {
            return $link;
        }

        // If user is admin, keep link as is
        if ($user->isAdmin()) {
            return $link;
        }

        // Convert URL paths based on role
        if ($user->isStaff()) {
            // Convert /admin/ to /staff/ in URL
            if (strpos($link, '/admin/') !== false) {
                $link = str_replace('/admin/', '/staff/', $link);
            }
            return $link;
        }

        if ($user->isStudent()) {
            // Convert /admin/ or /staff/ to /student/ in URL
            if (strpos($link, '/admin/') !== false) {
                $link = str_replace('/admin/', '/student/', $link);
            }
            if (strpos($link, '/staff/') !== false) {
                $link = str_replace('/staff/', '/student/', $link);
            }
            return $link;
        }

        return $link;
    }

    /**
     * Convert full URL to relative path
     */
    private static function convertToRelativePath($url)
    {
        if (!$url) {
            return null;
        }

        // If it's already a relative path, return as is
        if (!preg_match('/^https?:\/\//', $url)) {
            return $url;
        }

        // Extract path from full URL
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '/';
        
        // Add query string if exists
        if (isset($parsedUrl['query'])) {
            $path .= '?' . $parsedUrl['query'];
        }
        
        // Add fragment if exists
        if (isset($parsedUrl['fragment'])) {
            $path .= '#' . $parsedUrl['fragment'];
        }

        return $path;
    }
}

