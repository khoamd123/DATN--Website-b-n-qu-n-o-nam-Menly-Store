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
    ];
}
