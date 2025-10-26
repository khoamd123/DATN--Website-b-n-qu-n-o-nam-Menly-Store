<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'image_path',
        'media_type',
        'alt_text',
        'sort_order',
    ];

    /**
     * Get the event that owns the image
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the full URL for the image
     */
    public function getImageUrlAttribute()
    {
        return url('storage/' . $this->image_path);
    }
}
