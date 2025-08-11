<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'titre',
        'message',
        'data',
        'lu',
    ];

    protected $casts = [
        'data' => 'array',
        'lu' => 'boolean',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('lu', false);
    }

    /**
     * Scope to get read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('lu', true);
    }

    /**
     * Scope to get notifications by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead()
    {
        $this->lu = true;
        $this->save();
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread()
    {
        $this->lu = false;
        $this->save();
    }

    /**
     * Check if notification is read.
     */
    public function isRead()
    {
        return $this->lu;
    }

    /**
     * Check if notification is unread.
     */
    public function isUnread()
    {
        return !$this->lu;
    }
} 