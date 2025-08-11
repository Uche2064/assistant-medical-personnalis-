<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'expediteur_id',
        'contenu',
        'lu',
    ];

    protected $casts = [
        'lu' => 'boolean',
    ];

    /**
     * Get the conversation that owns the message.
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the sender of the message.
     */
    public function expediteur()
    {
        return $this->belongsTo(User::class, 'expediteur_id');
    }

    /**
     * Scope to get unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('lu', false);
    }

    /**
     * Scope to get read messages.
     */
    public function scopeRead($query)
    {
        return $query->where('lu', true);
    }

    /**
     * Mark message as read.
     */
    public function markAsRead()
    {
        $this->lu = true;
        $this->save();
    }

    /**
     * Mark message as unread.
     */
    public function markAsUnread()
    {
        $this->lu = false;
        $this->save();
    }

    /**
     * Check if message is read.
     */
    public function isRead()
    {
        return $this->lu;
    }

    /**
     * Check if message is unread.
     */
    public function isUnread()
    {
        return !$this->lu;
    }

    /**
     * Check if message is sent by a specific user.
     */
    public function isSentBy($userId)
    {
        return $this->expediteur_id == $userId;
    }
}