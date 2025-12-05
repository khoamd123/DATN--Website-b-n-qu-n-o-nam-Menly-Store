<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ClubResourceImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_resource_id',
        'image_path',
        'image_name',
        'image_type',
        'image_size',
        'thumbnail_path',
        'sort_order',
        'is_primary'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'image_size' => 'integer',
        'sort_order' => 'integer'
    ];

    // Relationship
    public function clubResource()
    {
        return $this->belongsTo(ClubResource::class);
    }

    // Helper methods
    public function getImageUrlAttribute()
    {
        if (!$this->image_path) {
            return asset('images/placeholder.jpg');
        }
        return asset('storage/' . $this->image_path);
    }

    public function getThumbnailUrlAttribute()
    {
        // Check if thumbnail exists, if not use original image
        if ($this->thumbnail_path && \Storage::exists('public/' . $this->thumbnail_path)) {
            return asset('storage/' . $this->thumbnail_path);
        }
        // Fallback to original image
        return $this->image_url;
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->image_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
