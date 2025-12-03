<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = [
        'sender_id',
        'title',
        'message',
    ];

    /**
     * Get the notification targets
     */
    public function targets()
    {
        return $this->hasMany(NotificationTarget::class);
    }

    /**
     * Get the notification reads
     */
    public function reads()
    {
        return $this->hasMany(NotificationRead::class);
    }
}
