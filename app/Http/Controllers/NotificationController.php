<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->limit(20)->get();
        $unreadCount = $user->unreadNotifications()->count();
        
        return response()->json([
            'notifications' => $notifications->map(function($notif) use ($user) {
                // Fix link based on user role
                $link = $notif->link;
                if ($link) {
                    $link = \App\Helpers\NotificationHelper::getRoleBasedLink($user, $link);
                }
                
                return [
                    'id' => $notif->id,
                    'type' => $notif->type,
                    'title' => $notif->title,
                    'message' => $notif->message,
                    'link' => $link,
                    'is_read' => $notif->is_read,
                    'created_at' => $notif->created_at->toISOString(),
                ];
            }),
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications()->update(['is_read' => true]);
        
        return response()->json(['success' => true]);
    }
}
