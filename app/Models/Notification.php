<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Notification
 * 
 * Menyimpan notifikasi untuk user (card reviewed, assigned, deadline, dll)
 * Mendukung realtime notification dengan Laravel Broadcasting
 */
class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Notification Types Constants
    const TYPE_CARD_REVIEWED = 'card_reviewed';
    const TYPE_CARD_ASSIGNED = 'card_assigned';
    const TYPE_DEADLINE_REMINDER = 'deadline_reminder';
    const TYPE_COMMENT_ADDED = 'comment_added';

    /**
     * Relationship: Notification belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Get unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: Get read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope: Get recent notifications (last 30 days)
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Get time ago string (2m ago, 5h ago, dll)
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get icon based on notification type
     */
    public function getIconAttribute()
    {
        return match($this->type) {
            self::TYPE_CARD_REVIEWED => '✅',
            self::TYPE_CARD_ASSIGNED => '📋',
            self::TYPE_DEADLINE_REMINDER => '⏰',
            self::TYPE_COMMENT_ADDED => '💬',
            default => '🔔',
        };
    }

    /**
     * Get color class based on notification type
     */
    public function getColorClassAttribute()
    {
        return match($this->type) {
            self::TYPE_CARD_REVIEWED => 'bg-green-100 text-green-600',
            self::TYPE_CARD_ASSIGNED => 'bg-blue-100 text-blue-600',
            self::TYPE_DEADLINE_REMINDER => 'bg-red-100 text-red-600',
            self::TYPE_COMMENT_ADDED => 'bg-purple-100 text-purple-600',
            default => 'bg-gray-100 text-gray-600',
        };
    }
}
