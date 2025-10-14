<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ClubResource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'club_id',
        'user_id',
        'title',
        'slug',
        'description',
        'resource_type',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'thumbnail_path',
        'external_link',
        'status',
        'download_count',
        'view_count',
        'tags'
    ];

    protected $casts = [
        'tags' => 'array',
        'download_count' => 'integer',
        'view_count' => 'integer',
        'file_size' => 'integer'
    ];

    /**
     * Get the club that owns the resource
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the user that uploaded the resource
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the file URL
     */
    public function getFileUrlAttribute()
    {
        if ($this->external_link) {
            return $this->external_link;
        }
        
        return $this->file_path ? Storage::url($this->file_path) : null;
    }

    /**
     * Get the thumbnail URL
     */
    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_path ? Storage::url($this->thumbnail_path) : null;
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Get resource type label
     */
    public function getResourceTypeLabelAttribute()
    {
        $labels = [
            'form' => '📋 Mẫu đơn',
            'image' => '🖼️ Hình ảnh',
            'video' => '🎥 Video',
            'pdf' => '📄 PDF',
            'document' => '📝 Tài liệu',
            'guide' => '📖 Hướng dẫn',
            'other' => '📦 Khác'
        ];

        return $labels[$this->resource_type] ?? '📦 Khác';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'active' => '✅ Hoạt động',
            'inactive' => '⏸️ Tạm dừng',
            'archived' => '📦 Lưu trữ'
        ];

        return $labels[$this->status] ?? '❓ Không xác định';
    }

    /**
     * Increment download count
     */
    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }

    /**
     * Increment view count
     */
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    /**
     * Scope for active resources
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for specific resource type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('resource_type', $type);
    }
}
