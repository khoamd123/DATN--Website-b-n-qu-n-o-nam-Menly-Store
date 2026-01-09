<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class VNPayService
{
    private $vnp_Url;
    private $vnp_TmnCode;
    private $vnp_HashSecret;
    private $vnp_ReturnUrl;

    public function __construct()
    {
        $this->vnp_Url = config('payment.vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        $this->vnp_TmnCode = config('payment.vnpay.tmn_code', '');
        $this->vnp_HashSecret = config('payment.vnpay.hash_secret', '');
        $returnUrl = config('payment.vnpay.return_url', '/payment/vnpay/return');
        // Nếu return_url là relative path, chuyển thành full URL
        $this->vnp_ReturnUrl = (strpos($returnUrl, 'http') === 0) ? $returnUrl : url($returnUrl);
    }

    /**
     * Tạo URL thanh toán VNPay
     */
    public function createPaymentUrl($payment)
    {
        // Kiểm tra cấu hình
        if (empty($this->vnp_TmnCode) || empty($this->vnp_HashSecret)) {
            Log::error('VNPay configuration missing: TMN Code or Hash Secret is empty');
            throw new \Exception('Cấu hình VNPay chưa đầy đủ. Vui lòng kiểm tra file .env');
        }

        $vnp_TxnRef = $payment->payment_code;
        $vnp_OrderInfo = $payment->description ?? 'Thanh toán đơn hàng ' . $payment->payment_code;
        // Giới hạn độ dài OrderInfo (VNPay yêu cầu tối đa 255 ký tự)
        $vnp_OrderInfo = mb_substr($vnp_OrderInfo, 0, 255);
        
        $vnp_OrderType = $this->getOrderType($payment->payment_type);
        $vnp_Amount = (int)($payment->amount * 100); // VNPay yêu cầu số tiền tính bằng xu (phải là số nguyên)
        $vnp_Locale = 'vn';
        $vnp_IpAddr = request()->ip() ?? '127.0.0.1';

        // Validate amount
        if ($vnp_Amount < 1000) { // Tối thiểu 1,000 xu = 10 VNĐ
            throw new \Exception('Số tiền thanh toán tối thiểu là 10 VNĐ');
        }

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->vnp_TmnCode,
            "vnp_Amount" => (string)$vnp_Amount, // VNPay yêu cầu string
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $this->vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        // Thêm thời gian hết hạn nếu có
        if ($payment->expires_at) {
            $inputData["vnp_ExpireDate"] = $payment->expires_at->format('YmdHis');
        }

        // Loại bỏ các giá trị rỗng
        $inputData = array_filter($inputData, function($value) {
            return $value !== null && $value !== '';
        });

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        // Tạo hash
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->vnp_HashSecret);
        
        $vnp_Url = $this->vnp_Url . "?" . $query . "vnp_SecureHash=" . $vnpSecureHash;

        // Log để debug (chỉ log trong môi trường local)
        if (config('app.debug')) {
            Log::info('VNPay Payment URL created', [
                'payment_code' => $payment->payment_code,
                'amount' => $vnp_Amount,
                'tmn_code' => substr($this->vnp_TmnCode, 0, 4) . '***', // Chỉ log một phần để bảo mật
            ]);
        }

        return $vnp_Url;
    }

    /**
     * Xác thực callback từ VNPay
     */
    public function verifyCallback($inputData)
    {
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash']);

        ksort($inputData);
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashdata, $this->vnp_HashSecret);

        return $secureHash === $vnp_SecureHash;
    }

    /**
     * Lấy loại đơn hàng theo payment_type
     */
    private function getOrderType($paymentType)
    {
        $types = [
            'event_registration' => '250000',
            'club_fee' => '250001',
            'fund_contribution' => '250002',
            'other' => 'other',
        ];

        $orderType = $types[$paymentType] ?? 'other';
        
        // VNPay yêu cầu OrderType phải là số hoặc 'other'
        // Nếu không phải số, dùng 'other'
        if (!is_numeric($orderType) && $orderType !== 'other') {
            return 'other';
        }
        
        return $orderType;
    }

    /**
     * Xử lý kết quả thanh toán từ VNPay
     */
    public function processCallback($inputData)
    {
        $vnp_ResponseCode = $inputData['vnp_ResponseCode'] ?? '';
        $vnp_TransactionStatus = $inputData['vnp_TransactionStatus'] ?? '';
        $vnp_TxnRef = $inputData['vnp_TxnRef'] ?? '';
        $vnp_Amount = ($inputData['vnp_Amount'] ?? 0) / 100; // Chuyển từ xu sang VNĐ
        $vnp_TransactionNo = $inputData['vnp_TransactionNo'] ?? '';
        $vnp_BankCode = $inputData['vnp_BankCode'] ?? '';

        $result = [
            'success' => false,
            'payment_code' => $vnp_TxnRef,
            'transaction_id' => $vnp_TransactionNo,
            'amount' => $vnp_Amount,
            'bank_code' => $vnp_BankCode,
            'response_code' => $vnp_ResponseCode,
            'message' => '',
        ];

        // Kiểm tra mã phản hồi
        if ($vnp_ResponseCode == '00' && $vnp_TransactionStatus == '00') {
            $result['success'] = true;
            $result['message'] = 'Thanh toán thành công';
        } else {
            $result['message'] = $this->getResponseMessage($vnp_ResponseCode);
        }

        return $result;
    }

    /**
     * Lấy thông báo lỗi từ mã phản hồi
     */
    private function getResponseMessage($responseCode)
    {
        $messages = [
            '00' => 'Giao dịch thành công',
            '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường).',
            '09' => 'Thẻ/Tài khoản chưa đăng ký dịch vụ InternetBanking',
            '10' => 'Xác thực thông tin thẻ/tài khoản không đúng. Quá 3 lần',
            '11' => 'Đã hết hạn chờ thanh toán. Xin vui lòng thực hiện lại giao dịch',
            '12' => 'Thẻ/Tài khoản bị khóa',
            '13' => 'Nhập sai mật khẩu xác thực giao dịch (OTP). Quá 5 lần',
            '51' => 'Tài khoản không đủ số dư để thực hiện giao dịch',
            '65' => 'Tài khoản đã vượt quá hạn mức giao dịch trong ngày',
            '75' => 'Ngân hàng thanh toán đang bảo trì',
            '79' => 'Nhập sai mật khẩu thanh toán quá số lần quy định',
            '99' => 'Lỗi không xác định',
        ];

        return $messages[$responseCode] ?? 'Lỗi không xác định (Mã: ' . $responseCode . ')';
    }
}

