<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id_1',
        'user_id_2',
        'dernier_message',
    ];

    /**
     * Get the first user in the conversation.
     */
    public function user1()
    {
        return $this->belongsTo(User::class, 'user_id_1');
    }

    /**
     * Get the second user in the conversation.
     */
    public function user2()
    {
        return $this->belongsTo(User::class, 'user_id_2');
    }

    /**
     * Get the messages for this conversation.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the latest message for this conversation.
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    /**
     * Get the other user in the conversation.
     */
    public function getOtherUser($currentUserId)
    {
        if ($this->user_id_1 == $currentUserId) {
            return $this->user2;
        }
        return $this->user1;
    }

    /**
     * Check if a user is part of this conversation.
     */
    public function hasUser($userId)
    {
        return $this->user_id_1 == $userId || $this->user_id_2 == $userId;
    }

    /**
     * Get unread messages count for a user.
     */
    public function getUnreadCount($userId)
    {
        return $this->messages()
                    ->where('expediteur_id', '!=', $userId)
                    ->where('lu', false)
                    ->count();
    }

    /**
     * Mark all messages as read for a user.
     */
    public function markAsRead($userId)
    {
        $this->messages()
             ->where('expediteur_id', '!=', $userId)
             ->where('lu', false)
             ->update(['lu' => true]);
    }
}