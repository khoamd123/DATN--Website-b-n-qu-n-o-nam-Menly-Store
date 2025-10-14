<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'club_id',
        'user_id',
        'title',
        'slug',
        'content',
        'type',
        'status',
    ];

    /**
     * Get the club that owns the post
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the user who created the post
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the comments for the post
     */
    public function comments()
    {
        return $this->hasMany(\App\Models\PostComment::class);
    }

    /**
     * Get the images for the post
     */
    public function images()
    {
        return $this->hasMany(\App\Models\PostImage::class);
    }

    /**
     * Get featured image
     */
    public function featuredImage()
    {
        return $this->hasOne(\App\Models\PostImage::class)->where('is_featured', true);
    }

    /**
     * Get gallery images
     */
    public function galleryImages()
    {
        return $this->hasMany(\App\Models\PostImage::class)->where('image_type', 'gallery')->ordered();
    }

    /**
     * Get all images ordered by sort order
     */
    public function orderedImages()
    {
        return $this->hasMany(\App\Models\PostImage::class)->ordered();
    }

    /**
     * Check if post has images
     */
    public function hasImages()
    {
        return $this->images()->exists();
    }

    /**
     * Get first image URL
     */
    public function getFirstImageUrlAttribute()
    {
        $image = $this->images()->first();
        return $image ? $image->image_url : null;
    }

    /**
     * Get featured image URL
     */
    public function getFeaturedImageUrlAttribute()
    {
        $image = $this->featuredImage;
        return $image ? $image->image_url : $this->first_image_url;
    }
}
