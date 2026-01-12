<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    /**
     * Get all conversations for the authenticated user
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get conversations where user is participant
        $conversations = Conversation::where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->with(['userOne', 'userTwo', 'latestMessage.sender'])
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($conversation) use ($user) {
                $otherUser = $conversation->getOtherUser($user->id);
                $unreadCount = $conversation->unreadMessagesCount($user->id);
                
                return [
                    'id' => $conversation->id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'email' => $otherUser->email,
                        'role' => $otherUser->role,
                    ],
                    'last_message' => $conversation->latestMessage ? [
                        'message' => $conversation->latestMessage->message,
                        'sender_id' => $conversation->latestMessage->sender_id,
                        'created_at' => $conversation->latestMessage->created_at,
                    ] : null,
                    'unread_count' => $unreadCount,
                    'last_message_at' => $conversation->last_message_at,
                ];
            });

        return view('chat.index', compact('conversations'));
    }

    /**
     * Get or create a conversation with another user
     */
    public function getConversation($userId)
    {
        $currentUser = Auth::user();
        $otherUser = User::findOrFail($userId);

        // Check permissions based on roles
        if (!$this->canChat($currentUser, $otherUser)) {
            return response()->json([
                'error' => 'You are not allowed to chat with this user.'
            ], 403);
        }

        $conversation = Conversation::getOrCreate($currentUser->id, $otherUser->id);
        
        return response()->json([
            'conversation_id' => $conversation->id,
            'other_user' => [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'email' => $otherUser->email,
                'role' => $otherUser->role,
            ],
        ]);
    }

    /**
     * Get messages for a conversation
     */
    public function getMessages($conversationId)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($conversationId);

        // Check if user is part of this conversation
        if ($conversation->user_one_id != $user->id && $conversation->user_two_id != $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Mark received messages as delivered (if not already)
        Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $user->id)
            ->where('delivery_status', 'sent')
            ->update([
                'delivery_status' => 'delivered',
                'delivered_at' => now(),
            ]);

        // Mark messages as read
        $readCount = 0;
        Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->each(function ($message) use (&$readCount) {
                $message->markAsRead();
                $readCount++;
            });

        $messages = Message::where('conversation_id', $conversationId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) use ($user) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name,
                    'message' => $message->message,
                    'is_read' => $message->is_read,
                    'delivery_status' => $message->delivery_status,
                    'is_mine' => $message->sender_id == $user->id,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $message->updated_at->format('Y-m-d H:i:s'),
                    'edited_at' => $message->edited_at ? $message->edited_at->format('Y-m-d H:i:s') : null,
                    'time' => $message->created_at->format('h:i A'),
                    'is_edited' => $message->isEdited(),
                    'can_edit' => $message->canEdit(),
                ];
            });

        // Get updated unread count for chat badge
        $conversations = Conversation::where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->get();
        $totalUnread = 0;
        foreach ($conversations as $conv) {
            $totalUnread += $conv->unreadMessagesCount($user->id);
        }

        return response()->json([
            'messages' => $messages,
            'other_user' => [
                'id' => $conversation->getOtherUser($user->id)->id,
                'name' => $conversation->getOtherUser($user->id)->name,
                'email' => $conversation->getOtherUser($user->id)->email,
                'role' => $conversation->getOtherUser($user->id)->role,
            ],
            'unread_count' => $totalUnread, // Updated unread count after marking as read
        ]);
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $user = Auth::user();
        $conversation = Conversation::findOrFail($conversationId);

        // Check if user is part of this conversation
        if ($conversation->user_one_id != $user->id && $conversation->user_two_id != $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $user->id,
            'message' => $request->message,
            'delivery_status' => 'sent',
        ]);

        // Update conversation's last message time
        $conversation->update([
            'last_message_at' => now(),
        ]);

        // Get recipient user
        $recipient = $conversation->getOtherUser($user->id);
        
        // Create notification for recipient
        \App\Helpers\NotificationHelper::createNotification(
            $recipient->id,
            'chat_message',
            'New Message from ' . $user->name,
            Str::limit($request->message, 100),
            route('chat.index') . '?conversation=' . $conversationId
        );

        // Broadcast the message
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'sender_name' => $message->sender->name,
                'message' => $message->message,
                'is_read' => $message->is_read,
                'delivery_status' => $message->delivery_status,
                'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                'time' => $message->created_at->format('h:i A'),
            ],
        ]);
    }

    /**
     * Get list of users the current user can chat with
     */
    public function getChattableUsers()
    {
        $user = Auth::user();
        $users = collect();

        if ($user->isAdmin()) {
            // Admin can chat with Staff
            $users = User::where('role', 'staff')
                ->where('id', '!=', $user->id)
                ->select('id', 'name', 'email', 'role')
                ->get();
        } elseif ($user->isStaff()) {
            // Staff can chat with Admin and Students
            $users = User::whereIn('role', ['admin', 'student'])
                ->where('id', '!=', $user->id)
                ->select('id', 'name', 'email', 'role')
                ->get();
        } elseif ($user->isStudent()) {
            // Students can only chat with Staff
            $users = User::where('role', 'staff')
                ->where('id', '!=', $user->id)
                ->select('id', 'name', 'email', 'role')
                ->get();
        }

        return response()->json($users);
    }

    /**
     * Check if two users can chat with each other
     */
    private function canChat($user1, $user2)
    {
        // Admin can chat with Staff
        if ($user1->isAdmin() && $user2->isStaff()) {
            return true;
        }
        if ($user1->isStaff() && $user2->isAdmin()) {
            return true;
        }

        // Staff can chat with Students
        if ($user1->isStaff() && $user2->isStudent()) {
            return true;
        }
        if ($user1->isStudent() && $user2->isStaff()) {
            return true;
        }

        return false;
    }

    /**
     * Mark message as delivered
     */
    public function markAsDelivered($messageId)
    {
        $user = Auth::user();
        $message = Message::findOrFail($messageId);
        
        // Check if user is the recipient
        $conversation = $message->conversation;
        if ($conversation->user_one_id != $user->id && $conversation->user_two_id != $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        if ($message->sender_id == $user->id) {
            return response()->json(['error' => 'Cannot mark own message as delivered'], 400);
        }
        
        $message->markAsDelivered();
        
        return response()->json([
            'success' => true,
            'delivery_status' => $message->delivery_status,
        ]);
    }

    /**
     * Delete message(s)
     */
    public function deleteMessages(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
        ]);

        $messages = Message::whereIn('id', $request->message_ids)
            ->where('sender_id', $user->id) // Users can only delete their own messages
            ->get();

        $deletedCount = 0;
        foreach ($messages as $message) {
            $conversation = $message->conversation;
            // Check if user is part of this conversation
            if ($conversation->user_one_id == $user->id || $conversation->user_two_id == $user->id) {
                $message->delete();
                $deletedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'deleted_count' => $deletedCount,
            'message' => $deletedCount > 0 ? "{$deletedCount} message(s) deleted successfully" : "No messages deleted"
        ]);
    }

    /**
     * Edit message
     */
    public function editMessage(Request $request, $messageId)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $user = Auth::user();
        $message = Message::findOrFail($messageId);

        // Check if user is the sender
        if ($message->sender_id != $user->id) {
            return response()->json(['error' => 'You can only edit your own messages'], 403);
        }

        // Check if user is part of this conversation
        $conversation = $message->conversation;
        if ($conversation->user_one_id != $user->id && $conversation->user_two_id != $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if message can still be edited (within 15 minutes like WhatsApp)
        if (!$message->canEdit()) {
            return response()->json([
                'error' => 'Message can only be edited within 15 minutes of sending',
                'can_edit' => false,
            ], 400);
        }

        // Update message
        $message->update([
            'message' => $request->message,
            'edited_at' => now(),
        ]);

        // Reload with sender relationship
        $message->load('sender');

        // Broadcast the updated message
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'sender_name' => $message->sender->name,
                'message' => $message->message,
                'is_read' => $message->is_read,
                'delivery_status' => $message->delivery_status,
                'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $message->updated_at->format('Y-m-d H:i:s'),
                'edited_at' => $message->edited_at ? $message->edited_at->format('Y-m-d H:i:s') : null,
                'time' => $message->created_at->format('h:i A'),
                'is_edited' => $message->isEdited(),
                'can_edit' => $message->canEdit(),
            ],
        ]);
    }

    /**
     * Get unread messages count
     */
    public function getUnreadCount()
    {
        $user = Auth::user();
        
        $conversations = Conversation::where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->get();

        $totalUnread = 0;
        foreach ($conversations as $conversation) {
            $totalUnread += $conversation->unreadMessagesCount($user->id);
        }

        return response()->json(['unread_count' => $totalUnread]);
    }
}
