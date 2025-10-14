<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $fillable = [
        'club_id',
        'created_by',
        'title',
        'slug',
        'description',
        'start_time',
        'end_time',
        'mode',
        'location',
        'max_participants',
        'status',
    ];

    /**
     * Get the club that owns the event
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the user who created the event
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
