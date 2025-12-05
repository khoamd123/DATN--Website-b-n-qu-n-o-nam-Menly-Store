<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundItem extends Model
{
    protected $fillable = ['fund_id','description','amount','status','rejection_reason'];

    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }
}