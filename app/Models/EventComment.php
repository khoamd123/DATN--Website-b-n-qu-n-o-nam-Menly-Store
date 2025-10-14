<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'parent_id',
        'content',
        'status',
    ];

    // Constants for status
    const STATUS_VISIBLE = 'visible';
    const STATUS_HIDDEN = 'hidden';
    const STATUS_DELETED = 'deleted';

    /**
     * Get the event that owns the comment
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user that owns the comment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment
     */
    public function parent()
    {
        return $this->belongsTo(EventComment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment
     */
    public function replies()
    {
        return $this->hasMany(EventComment::class, 'parent_id');
    }
}
