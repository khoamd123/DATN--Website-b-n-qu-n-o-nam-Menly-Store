<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Club extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'field_id',
        'leader_id',
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
     * Get the leader of the club
     */
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
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


    /**
     * Get active members of the club
     */
    public function activeMembers()
    {
        return $this->belongsToMany(User::class, 'club_members')
                    ->wherePivot('status', 'active');
    }

    /**
     * Get all club members
     */
    public function clubMembers()
    {
        return $this->hasMany(ClubMember::class);
    }

    /**
     * Get the join requests for the club.
     */
    public function joinRequests()
    {
        return $this->hasMany(ClubJoinRequest::class);
    }
}
