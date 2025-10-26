<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fund extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'initial_amount',
        'current_amount',
        'source',
        'status',
        'club_id',
        'created_by',
    ];

    protected $casts = [
        'initial_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
    ];

    /**
     * Quỹ thuộc về CLB nào
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Người tạo quỹ
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Giao dịch của quỹ
     */
    public function transactions()
    {
        return $this->hasMany(FundTransaction::class);
    }

    /**
     * Yêu cầu từ quỹ
     */
    public function requests()
    {
        return $this->hasMany(FundRequest::class);
    }

    /**
     * Tính tổng thu nhập (income)
     */
    public function getTotalIncome()
    {
        return $this->transactions()
            ->where('type', 'income')
            ->where('status', 'approved')
            ->sum('amount');
    }

    /**
     * Tính tổng chi tiêu (expense)
     */
    public function getTotalExpense()
    {
        return $this->transactions()
            ->where('type', 'expense')
            ->where('status', 'approved')
            ->sum('amount');
    }
}
