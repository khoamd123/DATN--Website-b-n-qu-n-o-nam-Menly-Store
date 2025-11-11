<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostComment extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'post_id',
        'parent_id',
        'content',
        'status',
        'deletion_reason',
        'deleted_at',
    ];

    /**
     * Relationship với Post
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Relationship với User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship với PostComment cha (nested comments)
     */
    public function parent()
    {
        return $this->belongsTo(PostComment::class, 'parent_id');
    }

    /**
     * Relationship với PostComment con (nested comments)
     */
    public function replies()
    {
        return $this->hasMany(PostComment::class, 'parent_id');
    }
}
