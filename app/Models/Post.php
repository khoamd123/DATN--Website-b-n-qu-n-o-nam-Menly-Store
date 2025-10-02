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
}
