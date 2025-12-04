<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'sender_id',
        'type',
        'title',
        'message',
        'read_at',
        'related_id',
        'related_type',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Get the notification targets
     */
    public function targets()
    {
        return $this->hasMany(NotificationTarget::class);
    }

    /**
     * Get the sender of the notification
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the related model (polymorphic)
     */
    public function related()
    {
        return $this->morphTo('related', 'related_type', 'related_id');
    }

    /**
     * Check if notification is read
     */
    public function isRead()
    {
        return $this->read_at !== null;
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }
}
