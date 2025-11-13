<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermissionsClub extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_permissions_club'; // Đảm bảo tên bảng khớp với migration

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', 'club_id', 'permission_id',
    ];

    /**
     * Get the user that owns the UserPermissionsClub record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the club that the UserPermissionsClub record belongs to.
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the permission associated with the UserPermissionsClub record.
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
