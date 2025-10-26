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
        'created_by'
    ];

    protected $casts = [
        'initial_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
    ];

    // Relationships
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions()
    {
        return $this->hasMany(FundTransaction::class);
    }

    public function requests()
    {
        return $this->hasMany(FundRequest::class);
    }

    // Helper methods
    public function getTotalIncome()
    {
        return $this->transactions()
            ->where('type', 'income')
            ->where('status', 'approved')
            ->sum('amount');
    }

    public function getTotalExpense()
    {
        return $this->transactions()
            ->where('type', 'expense')
            ->where('status', 'approved')
            ->sum('amount');
    }

    public function updateCurrentAmount()
    {
        $this->current_amount = $this->initial_amount + $this->getTotalIncome() - $this->getTotalExpense();
        $this->save();
    }

    // Accessor: Tự động lấy tên quỹ từ CLB
    public function getDisplayNameAttribute()
    {
        // Nếu có tên riêng, dùng tên riêng
        if ($this->name) {
            return $this->name;
        }
        
        // Nếu có CLB, dùng tên CLB
        if ($this->club) {
            return 'Quỹ của ' . $this->club->name;
        }
        
        // Mặc định
        return 'Quỹ chung hệ thống';
    }
}
