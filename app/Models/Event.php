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
        'image',
        'start_time',
        'end_time',
        'mode',
        'location',
        'max_participants',
        'status',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'cancelled_at' => 'datetime',
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

    /**
     * Get the images for the event
     */
    public function images()
    {
        return $this->hasMany(EventImage::class)->orderBy('sort_order');
    }

    /**
     * Get the main image for the event (first image or the old image field)
     */
    public function getMainImageAttribute()
    {
        $firstImage = $this->images()->first();
        if ($firstImage) {
            return $firstImage->image_url;
        }
        
        // Fallback to old image field
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        
        return null;
    }
}
