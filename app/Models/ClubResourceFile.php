<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubResourceFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_resource_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'thumbnail_path',
        'sort_order',
        'is_primary'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'file_size' => 'integer',
        'sort_order' => 'integer'
    ];

    // Relationship
    public function clubResource()
    {
        return $this->belongsTo(ClubResource::class);
    }

    // Helper methods
    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_path ? asset('storage/' . $this->thumbnail_path) : $this->file_url;
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileIconAttribute()
    {
        $type = $this->file_type;
        
        if (str_contains($type, 'word') || str_contains($type, 'document')) return 'fas fa-file-word text-primary';
        if (str_contains($type, 'excel') || str_contains($type, 'spreadsheet')) return 'fas fa-file-excel text-success';
        
        return 'fas fa-file text-muted';
    }
}
