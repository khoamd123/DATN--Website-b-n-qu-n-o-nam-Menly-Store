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
        'leader_id',
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
     * Get the leader of the club
     */
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * Get the members of the club
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'club_members')
            ->withPivot('position', 'status', 'joined_at');
    }

    /**
     * Get the club memberships
     */
    public function clubMembers()
    {
        return $this->hasMany(ClubMember::class);
    }

    /**
     * Get active members
     */
    public function activeMembers()
    {
        return $this->clubMembers()->active();
    }

    /**
     * Get leaders of the club
     */
    public function leaders()
    {
        return $this->clubMembers()->leaders();
    }

    /**
     * Get officers of the club
     */
    public function officers()
    {
        return $this->clubMembers()->officers();
    }

    /**
     * Get regular members of the club
     */
    public function regularMembers()
    {
        return $this->clubMembers()->members();
    }

    /**
     * Get join requests for the club
     */
    public function joinRequests()
    {
        return $this->hasMany(ClubJoinRequest::class);
    }

    /**
     * Get pending join requests
     */
    public function pendingJoinRequests()
    {
        return $this->joinRequests()->pending();
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
