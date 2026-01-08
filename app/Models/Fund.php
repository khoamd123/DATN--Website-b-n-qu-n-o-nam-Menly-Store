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
     * Các giao dịch của quỹ
     */
    public function transactions()
    {
        return $this->hasMany(FundTransaction::class);
    }

    /**
     * Các mục quỹ
     */
    public function items()
    {
        return $this->hasMany(FundItem::class);
    }

    /**
     * Cập nhật số tiền hiện tại
     */
    public function updateCurrentAmount()
    {
        $totalIncome = $this->transactions()
            ->where('type', 'income')
            ->where('status', 'approved')
            ->sum('amount');

        $totalExpense = $this->transactions()
            ->where('type', 'expense')
            ->where('status', 'approved')
            ->sum('amount');

        $this->current_amount = $this->initial_amount + $totalIncome - $totalExpense;
        $this->save();
    }

    /**
     * Kiểm tra quỹ có đang hoạt động không
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Lấy tổng thu của quỹ
     */
    public function getTotalIncome()
    {
        return $this->transactions()
            ->where('type', 'income')
            ->where('status', 'approved')
            ->sum('amount') ?? 0;
    }

    /**
     * Lấy tổng chi của quỹ
     */
    public function getTotalExpense()
    {
        return $this->transactions()
            ->where('type', 'expense')
            ->where('status', 'approved')
            ->sum('amount') ?? 0;
    }
}

