<?php
/**
 * Script tạm thời để chạy migration cho club_payment_qrs
 * CHỈ CHẠY MỘT LẦN, SAU ĐÓ XÓA FILE NÀY
 * 
 * Truy cập: http://localhost/DATN--Website-b-n-qu-n-o-nam-Menly-Store/public/run-migration-club-payment-qr.php
 */

// Load Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

try {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Chạy Migration</title></head><body>";
    echo "<h2>Đang chạy migrations...</h2>";
    echo "<pre>";
    
    // Chạy migration tạo bảng club_payment_qrs
    echo "1. Tạo bảng club_payment_qrs...\n";
    $exitCode1 = \Illuminate\Support\Facades\Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_01_20_000001_create_club_payment_qrs_table.php',
        '--force' => true
    ]);
    echo \Illuminate\Support\Facades\Artisan::output();
    
    // Chạy migration thêm fields vào fund_transactions
    echo "\n2. Thêm fields vào bảng fund_transactions...\n";
    $exitCode2 = \Illuminate\Support\Facades\Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_01_20_000002_add_payment_fields_to_fund_transactions_table.php',
        '--force' => true
    ]);
    echo \Illuminate\Support\Facades\Artisan::output();
    
    if ($exitCode1 === 0 && $exitCode2 === 0) {
        echo "\n✅ Migration hoàn tất thành công!\n";
        echo "</pre>";
        echo "<p style='color: green;'><strong>✅ Thành công! Bạn có thể truy cập trang quản lý QR code ngay bây giờ.</strong></p>";
        echo "<p><a href='/student/club-management/1/payment-qr'>Thử lại trang quản lý QR code</a></p>";
    } else {
        echo "\n⚠️ Có lỗi xảy ra. Vui lòng kiểm tra lại.\n";
        echo "</pre>";
    }
    
    echo "<p><strong style='color: red;'>Lưu ý:</strong> Vui lòng xóa file này sau khi chạy xong để bảo mật.</p>";
    echo "</body></html>";
    
} catch (Exception $e) {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Lỗi Migration</title></head><body>";
    echo "<h3 style='color: red;'>Lỗi:</h3>";
    echo "<pre style='color: red;'>" . htmlspecialchars($e->getMessage()) . "\n\n";
    echo htmlspecialchars($e->getTraceAsString()) . "</pre>";
    
    echo "<h3>Giải pháp thay thế:</h3>";
    echo "<p><strong>Cách 1:</strong> Chạy SQL trực tiếp trong phpMyAdmin:</p>";
    echo "<ol>";
    echo "<li>Mở phpMyAdmin (http://localhost/phpmyadmin)</li>";
    echo "<li>Chọn database <code>uniclubs</code></li>";
    echo "<li>Vào tab 'SQL'</li>";
    echo "<li>Copy nội dung file <code>create_club_payment_qrs_table.sql</code> và paste vào, sau đó click 'Go'</li>";
    echo "</ol>";
    
    echo "<p><strong>Cách 2:</strong> Chạy file batch:</p>";
    echo "<ol>";
    echo "<li>Double-click vào file <code>run-migrations.bat</code> trong thư mục project</li>";
    echo "</ol>";
    
    echo "<p><strong>Cách 3:</strong> Chạy từ Laragon Terminal:</p>";
    echo "<ol>";
    echo "<li>Mở Laragon</li>";
    echo "<li>Click menu → Terminal</li>";
    echo "<li>Chạy lệnh: <code>php artisan migrate</code></li>";
    echo "</ol>";
    echo "</body></html>";
}

