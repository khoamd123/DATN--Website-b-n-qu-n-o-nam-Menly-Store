<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClubPaymentQr extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'club_id',
        'payment_method',
        'account_number',
        'bank_code',
        'account_name',
        'qr_code_data',
        'qr_code_image',
        'is_primary',
        'is_active',
        'description',
        'created_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * CLB sở hữu QR code này
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Người tạo QR code (leader)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Lấy QR code primary của CLB
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true)->where('is_active', true);
    }

    /**
     * Scope: Lấy QR code đang hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Tạo QR code từ thông tin tài khoản (VietQR)
     */
    public function generateVietQR($amount = null, $description = null)
    {
        if ($this->payment_method !== 'VietQR') {
            return null;
        }

        // Sử dụng API VietQR để tạo QR code
        $apiUrl = 'https://img.vietqr.io/image/';
        $bankCode = strtolower($this->bank_code ?? 'tcb'); // TCB = Techcombank
        $accountNumber = $this->account_number;
        $accountName = $this->account_name ?? '';
        
        // Tạo URL với các tham số
        $params = [];
        $params['accountNo'] = $accountNumber;
        $params['accountName'] = $accountName;
        $params['bank'] = $bankCode;
        
        if ($amount && $amount >= 1000) {
            $params['amount'] = $amount;
        }

        if ($description) {
            $params['addInfo'] = $description;
        }

        // Tạo URL: https://img.vietqr.io/image/{bank}-{accountNumber}.png?{params}
        $qrUrl = $apiUrl . $bankCode . '-' . $accountNumber . '.png?' . http_build_query($params);
        
        return $qrUrl;
    }
}

