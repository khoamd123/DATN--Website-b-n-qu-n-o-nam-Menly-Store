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
        'image',
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
     * Get the attachments for the post
     */
    public function attachments()
    {
        return $this->hasMany(PostAttachment::class);
    }
}
