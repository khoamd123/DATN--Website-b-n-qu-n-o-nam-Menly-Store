<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventMemberEvaluation extends Model
{
    use HasFactory;
    protected $fillable = [
        'event_id',
        'club_id',
        'evaluator_id',
        'member_id',
        'score',
        'comment',

    ];
}
