<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    use HasFactory;
    protected $table = 'fields';
    protected $fillable = [
        'name',
        'description',
        'slug',
    ];

    /**
     * Get the clubs for the field
     */
    public function clubs()
    {
        return $this->hasMany(Club::class);
    }
}
