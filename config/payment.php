<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình các cổng thanh toán
    |
    */

    'vnpay' => [
        'url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'tmn_code' => env('VNPAY_TMN_CODE', ''),
        'hash_secret' => env('VNPAY_HASH_SECRET', ''),
        'return_url' => env('VNPAY_RETURN_URL', '/payment/vnpay/return'),
    ],

    'momo' => [
        'partner_code' => env('MOMO_PARTNER_CODE', ''),
        'access_key' => env('MOMO_ACCESS_KEY', ''),
        'secret_key' => env('MOMO_SECRET_KEY', ''),
        'return_url' => env('MOMO_RETURN_URL', '/payment/momo/return'),
    ],

    'default_method' => env('PAYMENT_DEFAULT_METHOD', 'vnpay'),
];

