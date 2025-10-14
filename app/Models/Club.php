<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'field_id',
        'owner_id',
        'max_members',
        'status'
    ];

    /**
     * Get the field that owns the club
     */
    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    /**
     * Get the owner of the club
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the members of the club
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'club_members');
    }

    /**
     * Get the events for the club
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the posts for the club
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
