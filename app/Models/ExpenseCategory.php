<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Transactions thuộc khoản mục này
     */
    public function transactions()
    {
        return $this->hasMany(FundTransaction::class, 'expense_category_id');
    }

    /**
     * Scope: Chỉ lấy khoản mục đang hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
