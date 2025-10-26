<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'requested_amount',
        'event_id',
        'club_id',
        'status',
        'approved_amount',
        'rejection_reason',
        'approval_notes',
        'created_by',
        'approved_by',
        'approved_at',
        'expense_items',
        'supporting_documents'
    ];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'expense_items' => 'array',
        'supporting_documents' => 'array',
    ];

    // Relationships
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isPartiallyApproved()
    {
        return $this->status === 'partially_approved';
    }

    public function approve($userId, $approvedAmount = null, $notes = null)
    {
        $this->status = $approvedAmount && $approvedAmount < $this->requested_amount ? 'partially_approved' : 'approved';
        $this->approved_by = $userId;
        $this->approved_amount = $approvedAmount ?? $this->requested_amount;
        $this->approval_notes = $notes;
        $this->approved_at = now();
        $this->save();
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
