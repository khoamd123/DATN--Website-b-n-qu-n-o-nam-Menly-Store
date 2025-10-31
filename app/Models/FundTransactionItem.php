<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundTransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'item_name',
        'amount',
        'notes',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Transaction mà item này thuộc về
     */
    public function transaction()
    {
        return $this->belongsTo(FundTransaction::class, 'transaction_id');
    }
}
