<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Notification extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'sender_id',
        'title',
        'message',
        'read_at',
        'type',
        'related_id',
        'related_type',
    ];
    
    /**
     * Override để chỉ fill các cột tồn tại trong database
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // Loại bỏ các cột không tồn tại khỏi attributes trước khi save
            $columnsToCheck = ['type', 'related_id', 'related_type', 'read_at'];
            foreach ($columnsToCheck as $column) {
                if (array_key_exists($column, $model->attributes)) {
                    if (!Schema::hasColumn('notifications', $column)) {
                        unset($model->attributes[$column]);
                    }
                }
            }
        });
    }

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

    /**
     * Get the notification reads
     */
    public function reads()
    {
        return $this->hasMany(NotificationRead::class);
    }
}
