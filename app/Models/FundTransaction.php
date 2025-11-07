<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'fund_id',
        'type',
        'transaction_type',
        'amount',
        'title',
        'description',
        'category',
        'transaction_date',
        'status',
        'rejection_reason',
        'receipt_path',
        'receipt_paths',
        'created_by',
        'approved_by',
        'approved_at',
        'event_id',
        'expense_category_id',
        'source'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'approved_at' => 'datetime',
        'receipt_paths' => 'array',
    ];

    // Relationships
    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function expenseCategory()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function items()
    {
        return $this->hasMany(FundTransactionItem::class, 'transaction_id');
    }

    // Helper methods
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function approve($userId)
    {
        $this->status = 'approved';
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->save();

        // Update fund current amount
        $this->fund->updateCurrentAmount();
    }

    public function reject($userId, $reason)
    {
        $this->status = 'rejected';
        $this->approved_by = $userId;
        $this->rejection_reason = $reason;
        $this->approved_at = now();
        $this->save();
    }
}
