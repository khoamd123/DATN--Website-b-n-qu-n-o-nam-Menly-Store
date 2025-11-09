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

    protected $casts = [
        'score' => 'integer',
    ];

    /**
     * Get the event that owns the evaluation
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the club that owns the evaluation
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the evaluator (user who made the evaluation)
     */
    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    /**
     * Get the member being evaluated
     */
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }
}
