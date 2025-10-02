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
}
