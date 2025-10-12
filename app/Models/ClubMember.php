<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'club_id',
        'role_in_club',
        'position',
        'status',
        'joined_at',
        'left_at'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    /**
     * Get the user that owns the membership
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the club that the user belongs to
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Scope for active members
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for pending members
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for leaders
     */
    public function scopeLeaders($query)
    {
        return $query->where('position', 'leader');
    }

    /**
     * Scope for officers (vice_president and officer)
     */
    public function scopeOfficers($query)
    {
        return $query->whereIn('position', ['vice_president', 'officer']);
    }

    /**
     * Scope for regular members
     */
    public function scopeMembers($query)
    {
        return $query->where('position', 'member');
    }

    /**
     * Check if member is leader
     */
    public function isLeader()
    {
        return $this->position === 'leader';
    }

    /**
     * Check if member is vice president
     */
    public function isVicePresident()
    {
        return $this->position === 'vice_president';
    }

    /**
     * Check if member is officer
     */
    public function isOfficer()
    {
        return $this->position === 'officer';
    }

    /**
     * Check if member is regular member
     */
    public function isMember()
    {
        return $this->position === 'member';
    }

    /**
     * Check if member can manage club (leader, vice_president, officer)
     */
    public function canManageClub()
    {
        return in_array($this->position, ['leader', 'vice_president', 'officer']);
    }

    /**
     * Get position display name
     */
    public function getPositionDisplayNameAttribute()
    {
        $positions = [
            'leader' => 'Trưởng CLB',
            'vice_president' => 'Phó CLB',
            'officer' => 'Cán sự',
            'member' => 'Thành viên'
        ];

        return $positions[$this->position] ?? $this->position;
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayNameAttribute()
    {
        $statuses = [
            'active' => 'Hoạt động',
            'pending' => 'Chờ duyệt',
            'inactive' => 'Không hoạt động'
        ];

        return $statuses[$this->status] ?? $this->status;
    }
}