<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone'	,
        'address',
        'avatar'
    ];
    protected $attributes = [
        'is_admin' => 0,
        'avatar' => '/images/avatar/avatar.png'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    /**
     * Get the clubs owned by the user
     */
    public function ownedClubs()
    {
        return $this->hasMany(Club::class, 'owner_id');
    }

    /**
     * Get the clubs where user is a member
     */
    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'club_members');
    }
}
