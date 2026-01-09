<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventViewer extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'viewed_at',
        'last_activity_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    /**
     * Get the event that owns the viewer
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user that is viewing
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if viewer is currently active (viewed within last 5 minutes)
     */
    public function isActive()
    {
        if (!$this->last_activity_at) {
            return false;
        }
        
        return $this->last_activity_at->diffInMinutes(now()) < 5;
    }
}

