<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message',
        'is_read',
        'read_at',
        'delivery_status',
        'delivered_at',
        'edited_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'delivered_at' => 'datetime',
        'edited_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function markAsDelivered()
    {
        if ($this->delivery_status === 'sent') {
            $this->update([
                'delivery_status' => 'delivered',
                'delivered_at' => now(),
            ]);
        }
    }

    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
                'delivery_status' => 'read',
            ]);
        } elseif ($this->delivery_status !== 'read') {
            $this->update([
                'delivery_status' => 'read',
            ]);
        }
    }

    public function getReadReceiptIcon()
    {
        if ($this->delivery_status === 'read') {
            return '<i class="fas fa-check-double text-primary"></i>'; // Double blue tick
        } elseif ($this->delivery_status === 'delivered') {
            return '<i class="fas fa-check-double text-muted"></i>'; // Double gray tick
        } else {
            return '<i class="fas fa-check text-muted"></i>'; // Single gray tick
        }
    }

    /**
     * Check if message can be edited (within 15 minutes like WhatsApp)
     */
    public function canEdit(): bool
    {
        // Messages can be edited within 15 minutes of creation
        $editTimeLimit = $this->created_at->copy()->addMinutes(15);
        return now()->isBefore($editTimeLimit);
    }

    /**
     * Check if message has been edited
     */
    public function isEdited(): bool
    {
        return $this->edited_at !== null;
    }
}
