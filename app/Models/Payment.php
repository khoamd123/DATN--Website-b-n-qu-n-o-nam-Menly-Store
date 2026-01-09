<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_code',
        'user_id',
        'fund_id',
        'event_id',
        'club_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'payment_type',
        'transaction_id',
        'bank_code',
        'payment_url',
        'callback_data',
        'description',
        'notes',
        'paid_at',
        'expires_at',
        'fund_transaction_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'callback_data' => 'array',
    ];

    /**
     * Boot method để tự động tạo payment_code
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_code)) {
                $payment->payment_code = 'PAY' . date('Ymd') . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * User thực hiện thanh toán
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quỹ liên quan
     */
    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }

    /**
     * Sự kiện liên quan
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * CLB liên quan
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Giao dịch quỹ liên quan
     */
    public function fundTransaction()
    {
        return $this->belongsTo(FundTransaction::class);
    }

    /**
     * Kiểm tra thanh toán đã hoàn thành chưa
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Kiểm tra thanh toán đang chờ xử lý
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Kiểm tra thanh toán đã hết hạn
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast() && !$this->isCompleted();
    }

    /**
     * Đánh dấu thanh toán thành công
     */
    public function markAsCompleted($transactionId = null, $callbackData = null)
    {
        $this->status = 'completed';
        $this->paid_at = now();
        if ($transactionId) {
            $this->transaction_id = $transactionId;
        }
        if ($callbackData) {
            $this->callback_data = $callbackData;
        }
        $this->save();
    }

    /**
     * Đánh dấu thanh toán thất bại
     */
    public function markAsFailed($reason = null)
    {
        $this->status = 'failed';
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . 'Lý do thất bại: ' . $reason;
        }
        $this->save();
    }

    /**
     * Đánh dấu thanh toán đã hủy
     */
    public function markAsCancelled($reason = null)
    {
        $this->status = 'cancelled';
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . 'Lý do hủy: ' . $reason;
        }
        $this->save();
    }
}
