<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubMember extends Model
{

    use HasFactory;
    protected $table = 'club_members';
    protected $fillable = [
        'club_id',
        'user_id',
        'role_in_club',
        'status',
        'joined_at',
        'left_at',
        'deletion_reason',
    ];

    /**
     * Get the club that the member belongs to.
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the user that is the member.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
