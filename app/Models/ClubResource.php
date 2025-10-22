<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClubResource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'resource_type',
        'club_id',
        'user_id',
        'status',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'thumbnail_path',
        'external_link',
        'tags',
        'view_count',
        'download_count'
    ];

    protected $casts = [
        'tags' => 'array',
        'file_size' => 'integer',
        'view_count' => 'integer',
        'download_count' => 'integer'
    ];

    /**
     * Get the club that owns the resource
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the user who created the resource
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Increment view count
     */
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    /**
     * Increment download count
     */
    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }

    /**
     * Get the file URL
     */
    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return null;
    }

    /**
     * Get the thumbnail URL
     */
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            return asset('storage/' . $this->thumbnail_path);
        }
        return null;
    }

    /**
     * Scope for active resources
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for resources by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('resource_type', $type);
    }
}
