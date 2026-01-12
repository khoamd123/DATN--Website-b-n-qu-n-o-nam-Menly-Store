<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClubMember extends Model
{

    use HasFactory, SoftDeletes;
    protected $table = 'club_members';
    protected $fillable = [
        'club_id',
        'user_id',
        'role_in_club',
        'position',
        'status',
        'joined_at',
        'left_at',
        'left_reason',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'position' => 'string', // Cast position as string to ensure proper handling
    ];

    /**
     * Get the club that owns the member
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the user that owns the member
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
