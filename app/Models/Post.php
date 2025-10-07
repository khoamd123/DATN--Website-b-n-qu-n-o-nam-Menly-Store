<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
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
}
