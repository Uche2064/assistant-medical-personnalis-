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
        'est_lu',
        'lu_a',
    ];

    protected $casts = [
        'data' => 'array',
        'est_lu' => 'boolean',
        'lu_a' => 'date',
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
        return $query->where('est_lu', false);
    }

    /**
     * Scope to get read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('est_lu', true);
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
        $this->est_lu = true;
        $this->lu_a = now();
        $this->save();
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread()
    {
        $this->est_lu = false;
        $this->save();
    }

    /**
     * Check if notification is read.
     */
    public function isRead()
    {
        return $this->est_lu;
    }

    /**
     * Check if notification is unread.
     */
    public function isUnread()
    {
        return !$this->est_lu;
    }
} 