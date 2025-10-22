<?php

// ...existing code...
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fund extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title','amount','transaction_type','club_id','user_id','content','status',
        'voucher_path','approved_by','approved_at','approved_amount','approval_note'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_amount' => 'decimal:2'
    ];

    public function items() { return $this->hasMany(FundItem::class); }
    public function club() { return $this->belongsTo(Club::class); }
    public function user() { return $this->belongsTo(User::class); }
}